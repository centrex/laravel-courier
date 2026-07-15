<?php

declare(strict_types = 1);

namespace Centrex\Courier\Services;

use Centrex\Courier\Exceptions\CourierException;

class RedxService extends AbstractCourierService
{
    public function track(string $trackingNumber): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), "parcel/track/{$trackingNumber}")),
        );
    }

    public function parcelInfo(string $trackingNumber): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), "parcel/info/{$trackingNumber}")),
        );
    }

    public function getAreas(): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), 'areas')),
        );
    }

    public function getAreaById(int $areaId): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), "areas/{$areaId}")),
        );
    }

    public function getAreaByPostCode(string $postCode): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), 'areas'), ['postcode' => $postCode]),
        );
    }

    public function getAreaByDistrictName(string $areaName): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), 'areas'), ['district_name' => $areaName]),
        );
    }

    public function getPickupStores(): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), 'pickup/stores')),
        );
    }

    public function getPickupStoreById(int $storeId): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), "pickup/store/info/{$storeId}")),
        );
    }

    /**
     * Redx's charge calculator is a GET endpoint taking its inputs as query
     * parameters (per the official docs) — not a POST body.
     * Returns e.g. ['deliveryCharge' => 60, 'codCharge' => 0].
     */
    public function calculateCharge(array $data): array
    {
        $this->requireFields($data, [
            'delivery_area_id', 'pickup_area_id', 'cash_collection_amount', 'weight',
        ]);

        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->get($this->buildUrl($this->baseUrl(), 'charge/charge_calculator'), $data),
        );
    }

    /**
     * Create a parcel (consignment) with Redx.
     *
     * Payload shape follows Redx's merchant "Create Parcel" API: recipient details,
     * delivery area id (from Redx's own area lookup — not provided by this package),
     * cash-on-delivery amount, parcel weight/value, and a details breakdown.
     */
    public function createParcel(array $data): array
    {
        $this->requireFields($data, [
            'customer_name', 'customer_phone', 'delivery_area_id', 'pickup_area_id',
            'customer_address', 'merchant_invoice_id', 'cash_collection_amount',
            'parcel_weight', 'value', 'parcel_details_json',
        ]);

        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->acceptJson()
                ->post($this->buildUrl($this->baseUrl(), 'parcel'), $data),
        );
    }

    public function priceChart(array $filters = []): array
    {
        $path = (string) data_get($this->config('redx', []), 'price_chart_csv', '');

        if ($path === '' || !is_file($path)) {
            throw new CourierException('Redx price chart CSV file not found.');
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new CourierException('Unable to open Redx price chart CSV file.');
        }

        $header = fgetcsv($handle);

        if (!is_array($header)) {
            fclose($handle);

            throw new CourierException('Redx price chart CSV header is invalid.');
        }

        $header = array_map(
            fn (string $value): string => trim(str_replace("\xEF\xBB\xBF", '', $value)),
            $header,
        );

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === []) {
                continue;
            }

            $item = array_combine($header, $row);

            if ($item === false) {
                continue;
            }

            if ($this->matchesFilters($item, $filters)) {
                $rows[] = $item;
            }
        }

        fclose($handle);

        return $rows;
    }

    protected function headers(): array
    {
        $token = (string) data_get($this->config('redx', []), 'api_access_token', '');

        return [
            'API-ACCESS-TOKEN' => 'Bearer ' . $token,
        ];
    }

    protected function baseUrl(): string
    {
        $config = $this->config('redx', []);
        $baseUrls = (array) data_get($config, 'base_urls', []);
        $environment = data_get($config, 'sandbox', false) ? 'sandbox' : 'live';
        $baseUrl = $baseUrls[$environment] ?? null;

        return rtrim((string) $baseUrl, '/');
    }

    protected function buildUrl(string $baseUrl, string $suffix = ''): string
    {
        $baseUrl = rtrim($baseUrl, '/');

        if ($suffix === '') {
            return $baseUrl;
        }

        return sprintf('%s/%s', $baseUrl, ltrim($suffix, '/'));
    }

    protected function matchesFilters(array $row, array $filters): bool
    {
        $map = [
            'district'      => 'District',
            'area'          => 'Area',
            'post_code'     => 'Post Code',
            'home_delivery' => 'Home Delivery',
            'lockdown'      => 'Lockdown',
        ];

        foreach ($map as $filterKey => $column) {
            $value = $filters[$filterKey] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $rowValue = (string) ($row[$column] ?? '');
            $filterValue = (string) $value;

            if (in_array($filterKey, ['district', 'area'], true)) {
                if (!str_contains(mb_strtolower($rowValue), mb_strtolower($filterValue))) {
                    return false;
                }

                continue;
            }

            if (mb_strtolower($rowValue) !== mb_strtolower($filterValue)) {
                return false;
            }
        }

        return true;
    }
}

<?php

declare(strict_types = 1);

namespace Centrex\Courier\Services;

use Centrex\Courier\Exceptions\CourierException;
use Centrex\Courier\Helpers\TokenManager;

class PathaoService extends AbstractCourierService
{
    public function track(string $trackingNumber, string $phone): array
    {
        $trackingUrl = (string) data_get($this->pathaoConfig(), 'tracking_url');

        return $this->json(
            $this->http->withHeaders([
                'accept'       => 'application/json, text/plain, */*',
                'content-type' => 'application/json',
                'origin'       => 'https://merchant.pathao.com',
                'referer'      => "https://merchant.pathao.com/tracking?consignment_id={$trackingNumber}&phone={$phone}",
            ])->post($trackingUrl, [
                'consignment_id' => $trackingNumber,
                'phone_no'       => $phone,
            ]),
        );
    }

    public function cities(): array
    {
        return $this->get('aladdin/api/v1/city-list')['data']['data'] ?? [];
    }

    public function zones(int $cityId): array
    {
        return $this->get("aladdin/api/v1/cities/{$cityId}/zone-list")['data']['data'] ?? [];
    }

    public function areas(int $zoneId): array
    {
        return $this->get("aladdin/api/v1/zones/{$zoneId}/area-list")['data']['data'] ?? [];
    }

    public function createStore(array $data): array
    {
        $this->requireFields($data, [
            'name', 'contact_name', 'contact_number',
            'address', 'city_id', 'zone_id', 'area_id',
        ]);

        return $this->post('aladdin/api/v1/stores', $data);
    }

    public function createOrder(array $data): array
    {
        $this->requireFields($data, [
            'store_id', 'recipient_name', 'recipient_phone',
            'recipient_address', 'delivery_type', 'item_type',
            'item_quantity', 'item_weight', 'amount_to_collect',
        ]);

        return $this->post('aladdin/api/v1/orders', $data);
    }

    public function createBulkOrder(array $orders): array
    {
        if ($orders === []) {
            throw new CourierException('Orders array cannot be empty');
        }

        return $this->post('aladdin/api/v1/orders/bulk', ['orders' => $orders]);
    }

    public function orderInfo(string $consignmentId): array
    {
        return $this->get("aladdin/api/v1/orders/{$consignmentId}/info");
    }

    public function priceCalculator(array $data): array
    {
        $this->requireFields($data, [
            'store_id', 'item_type', 'delivery_type',
            'item_weight', 'recipient_city', 'recipient_zone',
        ]);

        return $this->post('aladdin/api/v1/merchant/price-plan', $data);
    }

    public function storeInfo(): array
    {
        return $this->get('aladdin/api/v1/stores')['data']['data'] ?? [];
    }

    protected function get(string $endpoint): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->get($this->merchantUrl($endpoint)),
        );
    }

    protected function post(string $endpoint, array $body): array
    {
        return $this->json(
            $this->http
                ->withHeaders($this->headers())
                ->post($this->merchantUrl($endpoint), $body),
        );
    }

    protected function headers(): array
    {
        return [
            ...TokenManager::authorizationHeader($this->http, 'pathao', $this->pathaoConfig()),
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
    }

    protected function merchantUrl(string $endpoint): string
    {
        return sprintf('%s/%s', rtrim($this->baseUrl(), '/'), ltrim($endpoint, '/'));
    }

    protected function baseUrl(): string
    {
        $config = $this->pathaoConfig();
        $baseUrls = $config['base_urls'] ?? [
            'sandbox' => 'https://courier-api-sandbox.pathao.com/',
            'live'    => 'https://courier-api.pathao.com/',
        ];

        return ($config['sandbox'] ?? true)
            ? $baseUrls['sandbox']
            : $baseUrls['live'];
    }

    protected function pathaoConfig(): array
    {
        return $this->config('pathao', []);
    }

    protected function requireFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new CourierException("Missing required field: {$field}");
            }
        }
    }
}

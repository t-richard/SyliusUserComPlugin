<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusUserComPlugin\Api;

use BitBag\SyliusUserComPlugin\Trait\UserComApiAwareInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProductApi extends AbstractClient implements ProductApiInterface
{
    public function createProductEventByCustomId(
        UserComApiAwareInterface $resource,
        string $productId,
        array $payload,
        string $productName,
    ): ?array {
        $url = $this->getApiEndpointUrl($resource, sprintf(self::CREATE_PRODUCT_EVENT_BY_CUSTOM_ID_ENDPOINT, $productId));

        $response = $this->request(
            $url,
            Request::METHOD_POST,
            $this->buildOptions($resource, ['json' => $payload]),
        );

        if ($response === null) {
            return null;
        }

        if (!array_key_exists(self::ERROR, $response)) {
            return $response;
        }

        $this->createProduct($resource, [
            'custom_id' => $productId,
            'name' => $productName,
        ]);

        return $this->request(
            $url,
            Request::METHOD_POST,
            $this->buildOptions($resource, ['json' => $payload]),
        );
    }

    public function createProduct(UserComApiAwareInterface $resource, array $payload): array|null
    {
        $url = $this->getApiEndpointUrl($resource, self::CREATE_PRODUCT_ENDPOINT);

        return $this->request(
            $url,
            Request::METHOD_POST,
            $this->buildOptions($resource, ['json' => $payload]),
        );
    }
}

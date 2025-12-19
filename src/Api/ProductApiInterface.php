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

interface ProductApiInterface
{
    public const CREATE_PRODUCT_EVENT_BY_CUSTOM_ID_ENDPOINT = '/products-by-id/%s/product_event/';

    public const CREATE_PRODUCT_ENDPOINT = '/products/';

    public function createProductEventByCustomId(
        UserComApiAwareInterface $resource,
        string $productId,
        array $payload,
        string $productName,
    ): ?array;
}

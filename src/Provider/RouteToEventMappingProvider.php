<?php

namespace BitBag\SyliusUserComPlugin\Provider;

final class RouteToEventMappingProvider implements RouteToEventMappingProviderInterface
{
    public function getRouteMapping(): array
    {
        return [
            'sylius_customer_profile' => 'customer_profile_update',
            'sylius_admin_customer_update' => 'admin_customer_update',
            'sylius_shop_account_profile_update' => 'shop_customer_update',
            'sylius_shop_account_address_book_set_as_default' => 'shop_customer_default_address_update',
            'sylius_shop_register' => 'customer_registration',
            'sylius_shop_checkout_address' => 'customer_order_address_provided',
        ];
    }
}

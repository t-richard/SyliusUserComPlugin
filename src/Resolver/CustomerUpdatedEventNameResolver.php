<?php

namespace BitBag\SyliusUserComPlugin\Resolver;

use BitBag\SyliusUserComPlugin\Resolver\CustomerUpdatedEventNameResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class CustomerUpdatedEventNameResolver implements CustomerUpdatedEventNameResolverInterface
{
    public const DEFAULT_EVENT = 'undefined_event_name';

    public const ROUTE_TO_EVENT_MAP = [
        'sylius_customer_profile' => 'customer_profile_update',
        'sylius_admin_customer_update' => 'admin_customer_update',
        'sylius_shop_account_profile_update' => 'shop_customer_update',
        'sylius_shop_account_address_book_set_as_default' => 'shop_customer_default_address_update',
        'sylius_shop_register' => 'customer_registration',
        'sylius_shop_checkout_address' => 'customer_order_address_provided',
    ];

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function resolve(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return self::DEFAULT_EVENT;
        }

        $route = $request->attributes->get('_route');
        if (null === $route) {
            return self::DEFAULT_EVENT;
        }

        return
            is_string($route) &&
            array_key_exists($route, self::ROUTE_TO_EVENT_MAP)
                ? self::ROUTE_TO_EVENT_MAP[$route]
                : self::DEFAULT_EVENT
            ;
    }
}

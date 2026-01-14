<?php

namespace BitBag\SyliusUserComPlugin\Resolver;

use BitBag\SyliusUserComPlugin\Provider\RouteToEventMappingProviderInterface;
use BitBag\SyliusUserComPlugin\Resolver\CustomerUpdatedEventNameResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class RouteBasedCustomerUpdatedEventNameResolver implements CustomerUpdatedEventNameResolverInterface
{
    public const DEFAULT_EVENT = 'undefined_event_name';

    public function __construct(private RouteToEventMappingProviderInterface $routeToEventMappingProvider, private RequestStack $requestStack)
    {
    }

    public function resolve(): string
    {
        $routeToEventMap = $this->routeToEventMappingProvider->getRouteMapping();

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
            array_key_exists($route, $routeToEventMap)
                ? $routeToEventMap[$route]
                : self::DEFAULT_EVENT
            ;
    }
}

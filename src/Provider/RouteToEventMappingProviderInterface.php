<?php

namespace BitBag\SyliusUserComPlugin\Provider;

interface RouteToEventMappingProviderInterface
{
    /**
     * @return array<string, string
     */
    public function getRouteMapping(): array;
}

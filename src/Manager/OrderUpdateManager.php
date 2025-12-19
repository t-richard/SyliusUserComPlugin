<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusUserComPlugin\Manager;

use BitBag\SyliusUserComPlugin\Api\DealApiInterface;
use BitBag\SyliusUserComPlugin\Api\ProductApiInterface;
use BitBag\SyliusUserComPlugin\Builder\Payload\OrderPayloadBuilderInterface;
use BitBag\SyliusUserComPlugin\Builder\Payload\ProductEventPayloadBuilderInterface;
use BitBag\SyliusUserComPlugin\Provider\UserComApiAwareResourceProviderInterface;
use BitBag\SyliusUserComPlugin\Trait\UserComApiAwareInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

final class OrderUpdateManager implements OrderUpdateManagerInterface
{
    public function __construct(
        private readonly OrderPayloadBuilderInterface $orderPayloadBuilder,
        private readonly ProductEventPayloadBuilderInterface $productEventPayloadBuilder,
        private readonly DealApiInterface $dealApi,
        private readonly ProductApiInterface $productApi,
        private readonly LoggerInterface $logger,
        private readonly UserComApiAwareResourceProviderInterface $provider,
    ) {
    }

    public function handle(OrderInterface $order): void
    {
        $resource = $this->provider->getApiAwareResourceByOrder($order);
        if (null === $resource || null === $resource->getUserComUrl() || null === $resource->getUserComApiKey()) {
            return;
        }

        if (null === $order->getNumber()) {
            throw new \InvalidArgumentException('Order number cannot be null while sending order data to User.com');
        }

        $customer = $order->getCustomer();
        Assert::notNull($customer, 'Order customer cannot be null while sending order data to User.com');

        $email = $customer->getEmail();
        Assert::notNull($email, 'Order customer email cannot be null while sending order data to User.com');
        $email = strtolower($email);

        $this->createProductEvents($order, $resource, $email);

        if (OrderInterface::STATE_NEW === $order->getState()) {
            $this->dealApi->createDeal($resource, $this->orderPayloadBuilder->build($order), $email);

            return;
        }

        if (OrderInterface::STATE_FULFILLED === $order->getState() || OrderInterface::STATE_CANCELLED === $order->getState()) {
            $orderNumber = $order->getNumber();
            Assert::notNull($orderNumber, 'Order number cannot be null while sending order data to User.com');

            $this->dealApi->updateDealByCustomId(
                $resource,
                $orderNumber,
                $this->orderPayloadBuilder->build($order),
            );

            return;
        }

        throw new \RuntimeException(
            sprintf(
                'Order #%s state "%s" is not supported.',
                $order->getNumber(),
                $order->getState(),
            ),
        );
    }

    private function createProductEvents(
        OrderInterface $order,
        UserComApiAwareInterface $resource,
        string $email,
    ): void {
        try {
            $eventType = self::PRODUCT_EVENT_MAP[$order->getState()] ?? null;
            if (null === $eventType) {
                $this->logger->warning(sprintf('User.com - Order #%s state "%s" is not supported.', $order->getNumber(), $order->getState()));

                return;
            }

            foreach ($order->getItems() as $orderItem) {
                $variant = $orderItem->getVariant();
                if (null === $variant) {
                    $this->logger->warning(sprintf('User.com - Order item #%s does not have a variant.', $orderItem->getId()));

                    continue;
                }
                $product = $variant->getProduct();

                $this->productApi->createProductEventByCustomId(
                    $resource,
                    $variant->getCode(),
                    $this->productEventPayloadBuilder->build($eventType, $variant, $email),
                    $this->getProductName($variant, $product),
                );
            }
        } catch (\Throwable $exception) {
            $this->logger->error('User.com - Product event request failed.', [
                'exception' => $exception,
            ]);
        }
    }

    public function getProductName(
        ProductVariantInterface $variant,
        ?ProductInterface $product,
    ): string {
        $productName = $product?->getName();
        $variantName = $variant->getName();

        if (null === $productName) {
            return $variantName ?? 'Unknown product';
        }

        if (null === $variantName) {
            return $productName;
        }

        return sprintf('%s - %s', $productName, $variantName);
    }
}

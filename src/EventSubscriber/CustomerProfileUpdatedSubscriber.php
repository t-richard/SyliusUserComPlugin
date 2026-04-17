<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusUserComPlugin\EventSubscriber;

use BitBag\SyliusUserComPlugin\Dispatcher\CustomerMessageDispatcherInterface;
use BitBag\SyliusUserComPlugin\Manager\CookieManagerInterface;
use BitBag\SyliusUserComPlugin\Provider\UserComApiAwareResourceProviderInterface;
use BitBag\SyliusUserComPlugin\Resolver\CustomerUpdatedEventNameResolverInterface;
use BitBag\SyliusUserComPlugin\Trait\UserComApiAwareInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;

final class CustomerProfileUpdatedSubscriber implements CustomerProfileUpdatedSubscriberInterface, EventSubscriberInterface, ResetInterface
{
    public ?string $oldEmail = null;

    public function __construct(
        private readonly CustomerMessageDispatcherInterface        $customerMessageDispatcher,
        private readonly UserComApiAwareResourceProviderInterface  $userComApiAwareResourceProvider,
        private readonly CookieManagerInterface                    $cookieManager,
        private readonly CustomerUpdatedEventNameResolverInterface $customerUpdatedEventNameResolver,
        private readonly LoggerInterface                           $logger,
    ) {
    }

    #[\Override]
    public function dispatch(ResourceControllerEvent $event): void
    {
        $customer = $event->getSubject();
        if ($customer instanceof OrderInterface) {
            $customer = $customer->getCustomer();
        }

        if (!$customer instanceof CustomerInterface) {
            $this->logger->critical(
                'User.com - Subject of the event is not an instance of CustomerInterface.',
            );

            return;
        }

        $cookie = $this->cookieManager->getUserComCookie();
        $eventName = $this->customerUpdatedEventNameResolver->resolve();

        $resource = $this->userComApiAwareResourceProvider->getApiAwareResource();
        if (!$resource instanceof UserComApiAwareInterface || null === $resource->getId()) {
            $this->logger->critical(
                'User.com - Couldn\'t find resource for customer update or missing identifier.',
            );

            return;
        }

        $this->customerMessageDispatcher->dispatch(
            $eventName,
            $resource,
            $cookie,
            $customer,
            $customer->getDefaultAddress(),
            strtolower($this->oldEmail ?? $customer->getEmail() ?? ''),
        );
    }

    #[\Override]
    public function preUpdate(PreUpdateEventArgs $preUpdate): void
    {
        if (!$preUpdate->getObject() instanceof CustomerInterface || !$preUpdate->hasChangedField('email')) {
            return;
        }

        $this->oldEmail = $preUpdate->getOldValue('email');
    }

    #[\Override]
    public static function getSubscribedEvents()
    {
        return [
            'sylius.customer.post_register' => 'dispatch',
            'sylius.customer.post_update' => 'dispatch',
            'sylius.order.post_address' => 'dispatch',
        ];
    }

    #[\Override]
    public function reset(): void
    {
        $this->oldEmail = null;
    }
}

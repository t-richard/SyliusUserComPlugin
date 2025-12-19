<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusUserComPlugin\Form\Extension;

use BitBag\SyliusUserComPlugin\Manager\UserComApiTokenManagerInterface;
use BitBag\SyliusUserComPlugin\Trait\UserComApiAwareInterface;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

final class UserComApiAwareTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly UserComApiTokenManagerInterface $apiTokenManager,
    ) {
    }

    private const USER_COM_API_KEY_PROPERTY = 'userComApiKey';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'userComApiKey',
                PasswordType::class,
                [
                    'label' => 'bitbag_sylius_user_com_plugin.ui.user_com_api_key',
                    'required' => false,
                    'mapped' => false,
                    'always_empty' => false,
                ],
            )
            ->add(
                'userComUrl',
                TextType::class,
                [
                    'label' => 'bitbag_sylius_user_com_plugin.ui.user_com_url',
                    'required' => false,
                    'constraints' => [
                        new NotBlank(['groups' => ['sylius']]),
                        new Url(['groups' => ['sylius']]),
                    ],
                ],
            )
            ->add(
                'userComGTMContainerId',
                TextType::class,
                [
                    'label' => 'bitbag_sylius_user_com_plugin.ui.user_com_gtm',
                    'required' => false,
                ],
            )
            ->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                $apiToken = $form->get(self::USER_COM_API_KEY_PROPERTY)->getData();

                if (
                    null !== $apiToken &&
                    is_string($apiToken) &&
                    $data instanceof UserComApiAwareInterface
                ) {
                    $encryptedToken = $this->apiTokenManager->encrypt($apiToken);
                    $data->setUserComApiKey($encryptedToken);
                }
            });
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            ChannelType::class,
        ];
    }
}

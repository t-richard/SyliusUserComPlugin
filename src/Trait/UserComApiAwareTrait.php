<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusUserComPlugin\Trait;

use Doctrine\ORM\Mapping as ORM;

trait UserComApiAwareTrait
{
    #[ORM\Column(name: 'user_com_url', type: 'string', nullable: true)]
    /** @ORM\Column(name="user_com_url", type="string", nullable=true) */
    private ?string $userComUrl = null;

    #[ORM\Column(name: 'user_com_api_key', type: 'string', nullable: true)]
    /** @ORM\Column(name="user_com_api_key", type="string", nullable=true) */
    private ?string $userComApiKey = null;

    #[ORM\Column(name: 'user_com_gtm_container_id', type: 'string', nullable: true)]
    /** @ORM\Column(name="user_com_gtm_container_id", type="string", nullable=true) */
    private ?string $userComGTMContainerId = null;

    public function getUserComUrl(): ?string
    {
        return $this->userComUrl;
    }

    public function setUserComUrl(string $userComUrl): void
    {
        $this->userComUrl = $userComUrl;
    }

    public function getUserComApiKey(): ?string
    {
        return $this->userComApiKey;
    }

    public function setUserComApiKey(?string $userComApiKey): void
    {
        $this->userComApiKey = $userComApiKey;
    }

    public function getUserComGTMContainerId(): ?string
    {
        return $this->userComGTMContainerId;
    }

    public function setUserComGTMContainerId(?string $userComGTMContainerId): void
    {
        $this->userComGTMContainerId = $userComGTMContainerId;
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Authenticate;

/**
 * Class IdentityProvider
 *
 * @codeCoverageIgnore
 * @package Dvsa\Olcs\Cpms\Authenticate
 */
class CpmsIdentityProvider implements IdentityProviderInterface
{
    /** @var  string */
    protected $userId;
    /** @var  string */
    protected $clientId;
    /** @var  string */
    protected $clientSecret;
    /** @var  null|string */
    protected $customerReference;
    /** @var  string */
    protected $costCentre;

    public function __construct(string $userId, string $clientId, string $clientSecret)
    {
        $this->setUserId($userId);
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $customerReference
     */
    public function setCustomerReference(?string $customerReference): void
    {
        $this->customerReference = $customerReference;
    }

    /**
     * @return null|string
     */
    public function getCustomerReference(): ?string
    {
        return $this->customerReference;
    }

    public function setCostCentre(string $costCentre): void
    {
        $this->costCentre = $costCentre;
    }

    /**
     * @return string
     */
    public function getCostCentre(): string
    {
        return $this->costCentre;
    }
}

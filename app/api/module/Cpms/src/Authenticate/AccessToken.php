<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Authenticate;

/**
 * Class AccessToken
 *
 * @package Dvsa\Olcs\Cpms
 */
class AccessToken
{
    public const INVALID_ACCESS_TOKEN = 114;

    /** @var  string */
    protected $accessToken;
    /** @var int */
    protected $expiresIn;
    /** @var  int */
    protected $issuedAt;
    /** @var  string */
    protected $scope;
    /** @var  string */
    protected $tokenType;
    /** @var  string|null */
    protected $salesReference;

    /**
     * AccessToken constructor.
     */
    public function __construct(string $accessToken, int $expiresIn, int $issuedAt, string $scope, string $tokenType, ?string $salesReference = null)
    {
        $this->accessToken = $accessToken;
        $this->expiresIn = $expiresIn;
        $this->issuedAt = $issuedAt;
        $this->scope = $scope;
        $this->tokenType = $tokenType;
        $this->salesReference = $salesReference;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return int
     */
    public function getExpiresIn(): int
    {
        return (int) $this->expiresIn;
    }

    /**
     * Is token expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $expiryTime = (int)$this->getIssuedAt() + $this->getExpiresIn();

        return ($expiryTime < time());
    }

    /**
     * @return int
     */
    public function getIssuedAt(): int
    {
        return $this->issuedAt;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * Get Auth Header
     *
     * @return string
     */
    public function getAuthorisationHeader(): string
    {
        return 'Bearer ' . $this->getAccessToken();
    }
}

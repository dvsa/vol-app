<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Client;

/**
 * Class ClientOptions
 *
 * @codeCoverageIgnore
 */
class ClientOptions
{
    /** @var null | int */
    protected $version = null;
    /** @var  string */
    protected $grantType;
    /** @var float */
    protected $timeout = 30.00;
    /** @var string */
    protected $domain;
    /** @var array */
    protected $headers = [];


    public function __construct(?int $version, string $grantType, float $timeout, string $domain, array $headers = [])
    {
        $this->version = $version;
        $this->grantType = $grantType;
        $this->timeout = $timeout;
        $this->domain = $domain;
        $this->headers = $headers;
    }

    public function setTimeout(float $timeout): ClientOptions
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function setGrantType(string $grantType): ClientOptions
    {
        $this->grantType = $grantType;

        return $this;
    }

    /**
     * @return string
     */
    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function setDomain(string $domain): ClientOptions
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setHeaders(array $headers): ClientOptions
    {
        $this->headers = $headers;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setVersion(?int $version): void
    {
        $this->version = $version;
    }

    /**
     * @return int|null
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }
}

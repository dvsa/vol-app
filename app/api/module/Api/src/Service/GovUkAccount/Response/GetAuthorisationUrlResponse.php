<?php

namespace Dvsa\Olcs\Api\Service\GovUkAccount\Response;

class GetAuthorisationUrlResponse
{
    public function __construct(private readonly string $url, private readonly string $state, private readonly string $nonce)
    {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Ebsr;

/**
 * Class TransExchangeClient
 * @package Olcs\Ebsr\Service
 */
interface TransExchangeClientInterface
{
    /**
     * @param string $content
     * @return array
     */
    public function makeRequest($content);

    public function getCorrelationId(): string;
}

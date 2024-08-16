<?php

namespace Dvsa\Olcs\Api\Service\Nr;

/**
 * Class TransExchangeClient
 * @package Dvsa\Olcs\Api\Service\Nr
 */
interface InrClientInterface
{
    /**
     * @param string $content
     * @return int
     */
    public function makeRequest($content);
}

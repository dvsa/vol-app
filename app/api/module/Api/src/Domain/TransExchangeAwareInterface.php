<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient as TransExchangeClient;
use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClientInterface;

/**
 * TransExchangeAwareInterface
 */
interface TransExchangeAwareInterface
{
    /**
     * @param TransExchangeClientInterface $transExchange
     */
    public function setTransExchange(TransExchangeClientInterface $transExchange): void;

    /**
     * @return TransExchangeClientInterface
     */
    public function getTransExchange(): TransExchangeClientInterface;
}

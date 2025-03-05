<?php

namespace Dvsa\Olcs\Api\Service\Nr;

interface InrClientInterface
{
    public function makeRequest(string $xml): int;
}

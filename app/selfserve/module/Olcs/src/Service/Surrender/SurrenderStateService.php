<?php

namespace Olcs\Service\Surrender;

class SurrenderStateService
{
    private $surrenderData;

    public function __construct(array $surrenderData)
    {
        $this->surrenderData = $surrenderData;
    }

    public function fetchRoute(): string
    {
        return '';
    }
}
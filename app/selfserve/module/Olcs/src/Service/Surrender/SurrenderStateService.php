<?php

namespace Olcs\Service\Surrender;

class SurrenderStateService
{
    private $surrenderData;

    public function __construct($licenceId)
    {
        $this->surrenderData = $this->getSurrender($licenceId);
    }

    private function getSurrender($licenceId)
    {
        $response = $this->handleQuery(
            SurrenderQuery::create(['id' => $licenceId])
        );

        return $response->getResult();
    }
}
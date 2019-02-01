<?php

namespace Olcs\Service\Surrender;

use Common\RefData;

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

    private function getStatus()
    {
        return $this->surrenderData['status']['id'];
    }

    private function getCreatedOn()
    {
        return $this->surrenderData['createdOn'];
    }

    private function getModifiedOn()
    {
        return $this->surrenderData['lastModifiedOn'];
    }

    private function getGoodsDiscsOnLicence()
    {
        return $this->surrenderData['goodsDiscsOnLicence']['discCount'];
    }

    private function getPsvDiscsOnLicence()
    {
        return $this->surrenderData['psvDiscsOnLicence']['discCount'];
    }

    private function getDiscsOnSurrender()
    {
        $discDestroyed = $this->surrenderData['discDestroyed'] ?? 0;
        $discLost = $this->surrenderData['discLost'] ?? 0;
        $discStolen = $this->surrenderData['discStolen'] ?? 0;

        return $discDestroyed + $discLost + $discStolen;
    }

    private function getAddressLastModifiedOn()
    {
        return $this->surrenderData['addressLastModified'];
    }



}

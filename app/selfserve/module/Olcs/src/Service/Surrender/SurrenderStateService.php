<?php

namespace Olcs\Service\Surrender;

use Common\RefData;

class SurrenderStateService
{
    const STATE_EXPIRED = 'surrender_application_expired';
    const STATE_INFORMATION_CHANGED = 'surrender_application_changed';
    const STATE_WITHDRAWN = 'surrender_application_withdrawn';
    const STATE_OK = 'surrender_application_ok';

    private $surrenderData;

    public function setSurrenderData(array $surrenderData): SurrenderStateService
    {
        $this->surrenderData = $surrenderData;
        return $this;
    }

    public function getState(): string
    {
        if ($this->hasBeenWithdrawn()) {
            return static::STATE_WITHDRAWN;
        }

        if ($this->hasExpired()) {
            return static::STATE_EXPIRED;
        }

        if ($this->hasInformationChanged()) {
            return static::STATE_INFORMATION_CHANGED;
        }

        return static::STATE_OK;
    }

    public function fetchRoute(): string
    {
        $prefix = 'licence/surrender/';
        $suffix = '/GET';

        switch ($this->getStatus()) {
            case RefData::SURRENDER_STATUS_START:
                $page = 'review-contact-details';
                break;
            case RefData::SURRENDER_STATUS_CONTACTS_COMPLETE:
                $page = 'current-discs';
                break;
            case RefData::SURRENDER_STATUS_DISCS_COMPLETE:
                $page = 'operator-licence';
                break;
            case RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE:
                $page = $this->surrenderData['licence']['licenceType']['id'] === RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL ? 'community-licence' : 'review';
                break;
            case RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE:
            case RefData::SURRENDER_STATUS_DETAILS_CONFIRMED:
                $page = 'review';
                break;
            default:
                $prefix = '';
                $page = "lva-licence";
                $suffix = '';
                break;
        }

        return $prefix . $page . $suffix;
    }

    public function hasExpired(): bool
    {
        $now = new \DateTimeImmutable();
        $modified = $this->getSurrenderCreatedOrModifiedOn();

        return $now->diff($modified)->days >= 2;
    }

    public function getDiscsOnLicence(): int
    {
        return $this->surrenderData['licence']['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE ? $this->getGoodsDiscsOnLicence() : $this->getPsvDiscsOnLicence();
    }

    public function hasBeenWithdrawn(): bool
    {
        return $this->getStatus() === RefData::SURRENDER_STATUS_WITHDRAWN;
    }

    private function hasInformationChanged(): bool
    {
        $surrenderStatus = $this->getStatus();

        if ($surrenderStatus === RefData::SURRENDER_STATUS_START) {
            return false;
        }

        if (!$this->hasNotEnteredDiscInformation() && ($this->getDiscsOnSurrender() !== $this->getDiscsOnLicence())) {
            return true;
        }

        $surrenderModified = $this->getSurrenderCreatedOrModifiedOn();

        if (is_null($addressModified = $this->getAddressLastModifiedOn())) {
            return false;
        } elseif ($addressModified > $surrenderModified) {
            return true;
        }
        return false;
    }

    private function getStatus(): string
    {
        return $this->surrenderData['status']['id'];
    }

    private function getCreatedOn(): \DateTimeInterface
    {
        return new \DateTimeImmutable($this->surrenderData['createdOn']);
    }

    private function getModifiedOn(): ?\DateTimeInterface
    {
        return !is_null($this->surrenderData['lastModifiedOn']) ? new \DateTimeImmutable($this->surrenderData['lastModifiedOn']) : null;
    }

    private function getGoodsDiscsOnLicence(): int
    {
        return $this->surrenderData['goodsDiscsOnLicence']['discCount'];
    }

    private function getPsvDiscsOnLicence(): int
    {
        return $this->surrenderData['psvDiscsOnLicence']['discCount'];
    }

    private function getDiscsOnSurrender(): int
    {
        $discDestroyed = $this->surrenderData['discDestroyed'] ?? 0;
        $discLost = $this->surrenderData['discLost'] ?? 0;
        $discStolen = $this->surrenderData['discStolen'] ?? 0;

        return $discDestroyed + $discLost + $discStolen;
    }

    private function hasNotEnteredDiscInformation(): bool
    {
        return is_null($this->surrenderData['discDestroyed']) &&
            is_null($this->surrenderData['discLost']) &&
            is_null($this->surrenderData['discStolen']);
    }

    private function getAddressLastModifiedOn(): ?\DateTimeInterface
    {
        return !is_null($this->surrenderData['addressLastModified']) ? new \DateTimeImmutable($this->surrenderData['addressLastModified']) : null;
    }

    private function getSurrenderCreatedOrModifiedOn(): \DateTimeInterface
    {
        return $this->getModifiedOn() ?? $this->getCreatedOn();
    }
}

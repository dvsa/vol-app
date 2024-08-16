<?php

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section\Stub;

use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\AbstractReviewService;

/**
 * Stub class for testing AbstractReviewService
 */
class AbstractReviewServiceStub extends AbstractReviewService
{
    public function getConfig(TransportManagerApplication $tma)
    {
    }

    public function formatPersonFullName(Person $person)
    {
        return parent::formatPersonFullName($person);
    }

    public function formatDate($date)
    {
        return parent::formatDate($date);
    }

    public function formatFullAddress($address)
    {
        return parent::formatFullAddress($address);
    }

    public function formatShortAddress($address)
    {
        return parent::formatShortAddress($address);
    }

    public function findFiles($files, $category, $subCategory)
    {
        return parent::findFiles($files, $category, $subCategory);
    }

    public function translate($string)
    {
        return parent::translate($string);
    }

    public function translateReplace($translationKey, array $arguments)
    {
        return parent::translateReplace($translationKey, $arguments);
    }

    public function formatYesNo($value)
    {
        return parent::formatYesNo($value);
    }
}

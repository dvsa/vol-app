<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TransportManagerApplication\Section\Stub;

use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section\AbstractReviewService;

/**
 * Stub class for testing AbstractReviewService
 */
class AbstractReviewServiceStub extends AbstractReviewService
{
    public function getConfig(TransportManagerApplication $tma): void
    {
    }

    #[\Override]
    public function formatPersonFullName(Person $person): mixed
    {
        return parent::formatPersonFullName($person);
    }

    #[\Override]
    public function formatDate($date)
    {
        return parent::formatDate($date);
    }

    #[\Override]
    public function formatFullAddress($address)
    {
        return parent::formatFullAddress($address);
    }

    #[\Override]
    public function formatShortAddress($address)
    {
        return parent::formatShortAddress($address);
    }

    #[\Override]
    public function findFiles($files, $category, $subCategory)
    {
        return parent::findFiles($files, $category, $subCategory);
    }

    #[\Override]
    public function translate($string)
    {
        return parent::translate($string);
    }

    #[\Override]
    public function translateReplace($translationKey, array $arguments)
    {
        return parent::translateReplace($translationKey, $arguments);
    }

    #[\Override]
    public function formatYesNo($value)
    {
        return parent::formatYesNo($value);
    }
}

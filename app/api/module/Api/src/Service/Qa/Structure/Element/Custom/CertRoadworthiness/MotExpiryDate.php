<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThreshold;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class MotExpiryDate implements ElementInterface
{
    /**
     * Create instance
     *
     * @param bool $enableFileUploads
     *
     * @return MotExpiryDate
     */
    public function __construct(private $enableFileUploads, private readonly DateWithThreshold $dateWithThreshold)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'enableFileUploads' => $this->enableFileUploads,
            'dateWithThreshold' => $this->dateWithThreshold->getRepresentation(),
        ];
    }
}

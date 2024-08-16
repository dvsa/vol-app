<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common;

use DateTime;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\Date as DateElement;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;

class DateWithThreshold implements ElementInterface
{
    public const DATE_THRESHOLD_FORMAT = 'Y-m-d';

    /**
     * Create instance
     *
     *
     * @return DateWithThreshold
     */
    public function __construct(private readonly DateTime $dateThreshold, private readonly DateElement $date)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'dateThreshold' => $this->dateThreshold->format(self::DATE_THRESHOLD_FORMAT),
            'date' => $this->date->getRepresentation()
        ];
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Text;

class AnnualTripsAbroad implements ElementInterface
{
    /**
     * Create instance
     *
     * @param int $intensityWarningThreshold
     * @param bool $showNiWarning
     *
     * @return AnnualTripsAbroad
     */
    public function __construct(private $intensityWarningThreshold, private $showNiWarning, private readonly Text $text)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'intensityWarningThreshold' => $this->intensityWarningThreshold,
            'showNiWarning' => $this->showNiWarning,
            'text' => $this->text->getRepresentation(),
        ];
    }
}

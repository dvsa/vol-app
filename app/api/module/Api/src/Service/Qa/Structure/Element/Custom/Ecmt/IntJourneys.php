<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\Radio;

class IntJourneys implements ElementInterface
{
    /**
     * Create instance
     *
     * @param bool $showNiWarning
     *
     * @return IntJourneys
     */
    public function __construct(private $showNiWarning, private readonly Radio $radio)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        return [
            'showNiWarning' => $this->showNiWarning,
            'radio' => $this->radio->getRepresentation(),
        ];
    }
}

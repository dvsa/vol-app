<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\Radio;

class IntJourneysFactory
{
    /**
     * Create and return a IntJourneys instance
     *
     * @param bool $showNiWarning
     *
     * @return IntJourneys
     */
    public function create($showNiWarning, Radio $radio)
    {
        return new IntJourneys($showNiWarning, $radio);
    }
}

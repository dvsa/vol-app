<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;

class NoOfPermitsAvailableTextboxes
{
    public const LOOKUP = [
        Answer::BILATERAL_CABOTAGE_ONLY => [
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
        ],
        Answer::BILATERAL_STANDARD_AND_CABOTAGE => [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
        ],
        Answer::BILATERAL_STANDARD_ONLY => [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
        ],
    ];
}

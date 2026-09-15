<?php

declare(strict_types=1);

namespace Common\Test\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\Licence as LvaLicenceFormService;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

abstract class LicenceOperatingCentresTestCase extends OperatingCentresTestCase
{
    /**
     * @return void
     */
    #[\Override]
    protected function setUpDefaultServices()
    {
        parent::setUpDefaultServices();
    }
}

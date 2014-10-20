<?php

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\Form\Form;
use Common\Controller\Lva;
use Common\Service\Entity\LicenceEntityService;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use ApplicationControllerTrait;

    protected function getIdentifier()
    {
        return $this->getApplicationId();
    }
}

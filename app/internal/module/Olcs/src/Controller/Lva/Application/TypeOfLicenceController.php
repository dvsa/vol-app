<?php

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\Application\AbstractTypeOfLicenceController;

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait {
        ApplicationControllerTrait::getSectionsForView as genericGetSectionsForView;
    }

    protected $location = 'internal';
    protected $lva = 'application';
}

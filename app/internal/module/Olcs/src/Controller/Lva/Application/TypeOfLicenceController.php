<?php

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends Lva\AbstractTypeOfLicenceController
{
    use ApplicationControllerTrait {
            ApplicationControllerTrait::getSectionsForView as genericGetSectionsForView;
        }

    protected $location = 'internal';
    protected $lva = 'application';

    protected function getSectionsForView()
    {
        $data = $this->getServiceLocator()->get('Entity\Application')
            ->getTypeOfLicenceData($this->getApplicationId());

        $sections = $this->genericGetSectionsForView();

        if (empty($data['licenceType']) || empty($data['goodsOrPsv']) || empty($data['niFlag'])) {
            $sections['overview']['enabled'] = false;
        }

        return $sections;
    }
}

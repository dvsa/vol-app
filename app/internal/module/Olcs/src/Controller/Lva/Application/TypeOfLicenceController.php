<?php

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Zend\Stdlib\ResponseInterface;

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

    public function confirmationAction()
    {
        $adapter = $this->getTypeOfLicenceAdapter();

        // @NOTE will either return a redirect, or a form
        $response = $adapter->confirmationAction();

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return $this->render(
            'type_of_licence_confirmation',
            $response,
            array('sectionText' => 'application_type_of_licence_confirmation_subtitle')
        );
    }
}

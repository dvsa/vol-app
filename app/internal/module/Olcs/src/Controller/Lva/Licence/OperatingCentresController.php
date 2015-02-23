<?php

/**
 * Internal Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Zend\View\Model\ViewModel;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

/**
 * Internal Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController implements
    LicenceControllerInterface
{
    use LicenceControllerTrait,
        Lva\Traits\LicenceOperatingCentresControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    /**
     * Override add action to show variation warning
     */
    public function addAction()
    {
        // @NOTE The behaviour of this service differs internally to externally
        $processingService = $this->getServiceLocator()->get('Processing\CreateVariation');

        $request = $this->getController()->getRequest();

        $form = $processingService->getForm($request);

        if ($request->isPost() && $form->isValid()) {

            $data = $processingService->getDataFromForm($form);

            $data['licenceType'] = $this->getController()->params()->fromQuery('licence-type');

            $licenceId = $this->getController()->params('licence');

            $appId = $processingService->createVariation($licenceId, $data);

            $this->getServiceLocator()->get('Processing\VariationSection')
                ->setApplicationId($appId)
                ->completeSection('type_of_licence');

            return $this->getController()->redirect()->toRouteAjax('lva-variation', ['application' => $appId]);
        }

        //oc-create-variation

        return $form;

        //$view->setTemplate('licence/add-authorisation');
    }
}

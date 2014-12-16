<?php

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\AbstractController;

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationController extends AbstractController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $licenceId = $this->getIdentifier();

            $varId = $applicationService = $this->getServiceLocator()->get('Entity\Application')
                ->createVariation($licenceId);

            return $this->redirect()->toRouteAjax('lva-variation', ['application' => $varId]);
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('GenericConfirmation');
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        return $this->render(
            'create-variation-confirmation',
            $form,
            ['sectionText' => 'licence.variation.confirmation.text']
        );
    }
}

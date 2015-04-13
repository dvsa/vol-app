<?php

/**
 * External Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Zend\Form\Form;

/**
 * External Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use LicenceControllerTrait,
        Lva\Traits\CreateVariationTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    public function addAction()
    {
        $form = $this->processForm();

        // If we don't have an instance of Form, it should be a Response object, so we can just return it
        if (!($form instanceof Form)) {
            return $form;
        }

        return $this->render(
            'oc-create-variation-confirmation-title',
            $form,
            ['sectionText' => 'oc-create-variation-confirmation-message']
        );
    }

    public function deleteAction()
    {
        $ids = explode(',', $this->params('child_id'));
        $rows = $this->getAdapter()->getTableData();

        if (count($ids) >= count($rows)) {
            $request = $this->getRequest();

            if ($request->isPost()) {
                return $this->redirect()->toRouteAjax('create_variation', [], [], true);
            }

            $form = $this->getServiceLocator()->get('Helper\Form')
                ->createFormWithRequest('GenericConfirmation', $request);

            $form->get('form-actions')->get('submit')->setLabel('create-variation-button');

            $translator = $this->getServiceLocator()->get('Helper\Translation');

            $link = $this->getServiceLocator()->get('Helper\Url')
                ->fromRoute('lva-licence/variation', ['licence' => $this->getLicenceId()]);

            return $this->render(
                'create-variation-confirmation',
                $form,
                [
                    'sectionText' => $translator->translateReplace(
                        'variation-required-message-prefix',
                        [$link]
                    )
                ]
            );
        }

        return parent::deleteAction();
    }
}

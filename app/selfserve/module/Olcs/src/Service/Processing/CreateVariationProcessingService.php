<?php

/**
 * Create Variation Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Service\Processing;

use Zend\Form\Form;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Create Variation Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVariationProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function getForm(Request $request)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('GenericConfirmation');

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
        }

        $formHelper->setFormActionFromRequest($form, $request);

        $form->get('form-actions')->get('submit')->setLabel('create-variation-button');

        return $form;
    }

    public function createVariation($licenceId, $data)
    {
        return $this->getServiceLocator()->get('Entity\Application')->createVariation($licenceId, $data);
    }

    public function getDataFromForm(Form $form)
    {
        return [];
    }
}

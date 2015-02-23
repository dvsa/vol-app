<?php

/**
 * Create Variation Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Service\Processing;

use Common\Service\Data\FeeTypeDataService;
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

        $form = $formHelper->createForm('CreateVariation');

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
        } else {
            $dateHelper = $this->getServiceLocator()->get('Helper\Date');

            $form->setData(['data' => ['receivedDate' => $dateHelper->getDate()]]);
        }

        $formHelper->setFormActionFromRequest($form, $request);

        $form->get('form-actions')->get('submit')->setLabel('create-variation-button');

        return $form;
    }

    public function createVariation($licenceId, $data)
    {
        $feeRequired = $data['feeRequired'];

        unset($data['feeRequired']);

        $appId = $this->getServiceLocator()->get('Entity\Application')->createVariation($licenceId, $data);

        if ($feeRequired == 'Y') {
            // Create fee
            $applicationProcessingService = $this->getServiceLocator()->get('Processing\Application');
            $applicationProcessingService->createFee($appId, $licenceId, FeeTypeDataService::FEE_TYPE_VAR);
        }

        return $appId;
    }

    public function getDataFromForm(Form $form)
    {
        return $form->getData()['data'];
    }
}

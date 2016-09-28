<?php

/**
 * Create Variation Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Service\Processing;

use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
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
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('GenericConfirmation', $request);

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
        }

        $form->get('form-actions')->get('submit')->setLabel('save.continue.button');

        return $form;
    }

    public function createVariation($licenceId, $data)
    {
        $data['id'] = $licenceId;

        $command = CreateVariation::create($data);

        $annotationBuilder = $this->getServiceLocator()->get('TransferAnnotationBuilder');
        $commandService = $this->getServiceLocator()->get('CommandService');

        $command = $annotationBuilder->createCommand($command);
        $response = $commandService->send($command);

        if ($response->isOk()) {
            return $response->getResult()['id']['application'];
        }
    }

    public function getDataFromForm(Form $form)
    {
        return [];
    }
}

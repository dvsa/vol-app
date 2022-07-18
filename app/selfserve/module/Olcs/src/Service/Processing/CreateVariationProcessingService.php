<?php

namespace Olcs\Service\Processing;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Form\Form;
use Laminas\Http\Request;

/**
 * Create Variation Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateVariationProcessingService
{
    /** @var FormHelperService */
    protected $formHelper;

    /** @var AnnotationBuilder */
    protected $annotationBuilder;

    /** @var CommandService */
    protected $commandService;

    /**
     * Create service instance
     *
     * @param FormHelperService $formHelper
     * @param AnnotationBuilder $annotationBuilder
     * @param CommandService $commandService
     *
     * @return CreateVariationProcessingService
     */
    public function __construct(
        FormHelperService $formHelper,
        AnnotationBuilder $annotationBuilder,
        CommandService $commandService
    ) {
        $this->formHelper = $formHelper;
        $this->annotationBuilder = $annotationBuilder;
        $this->commandService = $commandService;
    }

    public function getForm(Request $request)
    {
        $form = $this->formHelper->createFormWithRequest('GenericConfirmation', $request);

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

        $command = $this->annotationBuilder->createCommand($command);
        $response = $this->commandService->send($command);

        if ($response->isOk()) {
            return $response->getResult()['id']['application'];
        }
    }

    /**
     * @param Form $form
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDataFromForm(Form $form)
    {
        return [];
    }
}

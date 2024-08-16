<?php

namespace Olcs\Service\Processing;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Data\FeeTypeDataService;
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

    /** @var DateHelperService */
    protected $dateHelper;

    /**
     * Create service instance
     *
     *
     * @return CreateVariationProcessingService
     */
    public function __construct(
        FormHelperService $formHelper,
        AnnotationBuilder $annotationBuilder,
        CommandService $commandService,
        DateHelperService $dateHelper
    ) {
        $this->formHelper = $formHelper;
        $this->annotationBuilder = $annotationBuilder;
        $this->commandService = $commandService;
        $this->dateHelper = $dateHelper;
    }

    public function getForm(Request $request)
    {
        $form = $this->formHelper->createFormWithRequest('CreateVariation', $request);

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
        } else {
            $form->setData(['data' => ['receivedDate' => $this->dateHelper->getDate()]]);
        }

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

    public function getDataFromForm(Form $form)
    {
        return $form->getData()['data'];
    }
}

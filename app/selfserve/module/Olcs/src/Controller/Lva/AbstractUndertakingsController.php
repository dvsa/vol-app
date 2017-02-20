<?php

namespace Olcs\Controller\Lva;

use Common\Service\Entity\LicenceEntityService as Licence;
use Common\RefData;
use Common\Controller\Lva\Traits\EnabledSectionTrait;
use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\Application\UpdateDeclaration;
use Common\Form\Form;

/**
 * External Abstract Undertakings Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractUndertakingsController extends AbstractController
{
    protected $location = 'external';

    protected $data = [];

    use EnabledSectionTrait;

    /**
     * Index action
     *
     * @return \Common\View\Model\Section|\Zend\Http\Response
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('change')) {
            return $this->goToOverview();
        }

        $request = $this->getRequest();
        $applicationData = $this->getUndertakingsData();
        $form = $this->updateForm($this->getForm(), $applicationData);

        $files = ['undertakings-interim'];
        if ($this->lva === 'application' && !$this->data['disableSignatures']) {
            $files[] = 'undertakings-verify';
        }
        $this->getServiceLocator()->get('Script')->loadFiles($files);

        if ($request->isPost()) {
            $data = (array) $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($this->isButtonPressed('submitAndPay') || $this->isButtonPressed('submit')) {
                    $shouldCompleteSection = true;
                } else {
                    $shouldCompleteSection = false;
                }
                $response = $this->save($form->getData(), $shouldCompleteSection);
                if ($response->isOk()) {
                    $this->completeSection('undertakings');
                    return $this->goToNextStep();
                }
            } else {
                // validation failed, we need to use the application data
                // for markup but use the POSTed values to render the form again
                $formData = array_replace_recursive(
                    $this->formatDataForForm($applicationData),
                    $data
                );
                // don't call setData again here or we lose validation messages
                $form->populateValues($formData);
            }
        } else {
            $data = $this->formatDataForForm($applicationData);
            $form->setData($data);
        }

        return $this->render('undertakings', $form);
    }

    /**
     * Save the form data
     *
     * @param array $formData              form data
     * @param bool  $shouldCompleteSection should complete section
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function save($formData, $shouldCompleteSection = false)
    {
        $dto = $this->createUpdateDeclarationDto($formData, $shouldCompleteSection);

        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);

        if (!$response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $response;
    }

    /**
     * Go to the next step
     *
     * @return \Zend\Http\Response
     */
    protected function goToNextStep()
    {
        if ($this->isButtonPressed('submitAndPay') || $this->isButtonPressed('submit')) {
            // section completed
            return $this->redirect()->toRoute(
                'lva-'.$this->lva . '/pay-and-submit',
                [$this->getIdentifierIndex() => $this->getIdentifier(), 'redirect-back' => 'undertakings'],
                [],
                true
            );
        } elseif ($this->isButtonPressed('sign')) {
            return $this->redirect()->toRoute(
                'verify/initiate-request',
                [$this->getIdentifierIndex() => $this->getIdentifier()]
            );
        }
    }

    /**
     * Create update declaration dto
     *
     * @param array $formData              form data
     * @param bool  $shouldCompleteSection should complete section
     *
     * @return UpdateDeclaration
     */
    protected function createUpdateDeclarationDto($formData, $shouldCompleteSection = true)
    {
        $signatureType = null;
        if ($this->lva === 'variation') {
            $signatureType = RefData::SIGNATURE_TYPE_NOT_REQUIRED;
        } elseif ($shouldCompleteSection) {
            $signatureType = RefData::SIGNATURE_TYPE_PHYSICAL_SIGNATURE;
        }

        $data = [
            'id' => $this->getIdentifier(),
            'version' => $formData['declarationsAndUndertakings']['version'],
            'declarationConfirmation' => $shouldCompleteSection ? 'Y' : 'N',
            'interimRequested' => isset($formData['interim']) ?
                $formData['interim']['goodsApplicationInterim'] : null,
            'interimReason' => isset($formData['interim']) ?
                $formData['interim']['goodsApplicationInterimReason'] : null
        ];
        if ($signatureType) {
            $data['signatureType'] = $signatureType;
        }
        $dto = UpdateDeclaration::create($data);

        return $dto;
    }

    /**
     * Get undertakings data
     *
     * @return array|false
     */
    protected function getUndertakingsData()
    {
        $query = \Dvsa\Olcs\Transfer\Query\Application\Declaration::create(['id' => $this->getIdentifier()]);

        $response =  $this->handleQuery($query);

        if ($response->isOk()) {
            $result = $response->getResult();
            $this->data = $result;
            return $result;
        }

        $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');

        return false;
    }

    /**
     * Format data for form
     *
     * @param array $applicationData application data
     *
     * @return array
     */
    protected function formatDataForForm($applicationData)
    {
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        $output = array(
            'declarationsAndUndertakings' => array(
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
            )
        );

        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $interim = array();
            if (!is_null($applicationData['interimReason'])) {
                $interim['goodsApplicationInterim'] = "Y";
                $interim['goodsApplicationInterimReason'] = $applicationData['interimReason'];
            }

            $output['interim'] = $interim;
        }

        return $output;
    }

    /**
     * Update submit buttons
     *
     * @param Form  $form            form
     * @param array $applicationData application data
     *
     * @return void
     */
    protected function updateSubmitButtons($form, $applicationData)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        if (!$this->isReadyToSubmit($applicationData)) {
            $formHelper->remove($form, 'form-actions->submitAndPay');
            $formHelper->remove($form, 'form-actions->submit');
            $formHelper->remove($form, 'form-actions->change');
            return;
        }

        if ($this->lva === 'application') {
            $formHelper->remove($form, 'form-actions->saveAndContinue');
        }
        $formHelper->remove($form, 'form-actions->save');
        $formHelper->remove($form, 'form-actions->cancel');

        if (floatval($applicationData['outstandingFeeTotal']) > 0) {
            $formHelper->remove($form, 'form-actions->submit');
        } else {
            $formHelper->remove($form, 'form-actions->submitAndPay');
        }
    }
}

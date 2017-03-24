<?php

namespace Olcs\Controller\Licence;

use Common\Service\Entity\ContinuationDetailEntityService;
use Common\Service\Entity\LicenceEntityService;
use Olcs\Controller\AbstractController;
use Olcs\Data\Mapper\Continuation as ContinuationMapper;
use Zend\View\Model\ViewModel;

/**
 * Continuation Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationController extends AbstractController
{
    /**
     * Process action - updateContinuation
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function updateContinuationAction()
    {
        $licenceId = (int) $this->params()->fromRoute('licence', null);

        $data = $this->getContinuationDetailData($licenceId);
        $continuationDetail = $data['continuationDetail'];
        $hasOutstandingContinuationFee = $data['hasOutstandingContinuationFee'];
        $numNotCeasedDiscs = $data['numNotCeasedDiscs'];

        /** @var \Common\Form\Form $form */
        $form = $this->getForm('UpdateContinuation');

        $this->alterForm($form, $continuationDetail, $hasOutstandingContinuationFee);
        $this->populateFormDefaultValues($form, $continuationDetail, $numNotCeasedDiscs);

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->isButtonPressed('printSeperator')) {
                $this->createSeparatorSheet($continuationDetail['licence']['licNo']);

                return $this->redirectToRoute('licence/update-continuation', [], [], true);
            }

            $this->formPost($form);
            //  there is hidden call of postSetFormData()

            if ($form->isValid()) {
                if ($this->isButtonPressed('submit')) {
                    $this->updateContinuation($continuationDetail, $form->getData());
                    $this->addSuccessMessage('update-continuation.saved');
                    return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
                }

                if ($this->isButtonPressed('continueLicence')) {
                    $this->updateContinuation($continuationDetail, $form->getData());
                    if ($this->continueLicence($continuationDetail['licence'], $form)) {
                        $this->addSuccessMessage('update-continuation.success');
                        return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
                    }
                }
            }
        }
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        $this->getServiceLocator()->get('Script')->loadFile('forms/update-continuation');

        return $this->renderView($view, 'Continue licence');
    }

    /**
     * Additional action with form after post.
     * - for UpdateContinuation:  add 'checklistStatus' field if it disabled in form
     *
     * @param \Common\Form\Form $form Form
     *
     * @return \Common\Form\Form
     */
    protected function postSetFormData(\Common\Form\Form $form)
    {
        if ($form->getName() === 'UpdateContinuation') {
            /** @var \Zend\Form\Fieldset $fields */
            $fields = $form->get('fields');

            if ($fields->get('received')->getValue() === 'N') {
                $form->getInputFilter()
                    ->get('fields')
                    ->get('checklistStatus')->setRequired(false);
            }
        }

        return $form;
    }

    /**
     * Create a Continuation Separator Sheet
     *
     * @param int $licNo Licence number
     *
     * @return void
     */
    private function createSeparatorSheet($licNo)
    {
        $response = $this->handleCommand(
            \Dvsa\Olcs\Transfer\Command\Scan\CreateContinuationSeparatorSheet::create(['licNo' => $licNo])
        );
        if ($response->isOk()) {
            $this->addSuccessMessage('update-continuation.separator-sheet');
        } else {
            $this->addErrorMessage('unknown-error');
        }
    }

    /**
     * Get the ContinuationDetail data
     *
     * @param int $licenceId
     *
     * @return array
     * @throws \RuntimeException
     */
    private function getContinuationDetailData($licenceId)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Licence\ContinuationDetail::create(['id' => $licenceId])
        );
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting licence continuation detail');
        }

        return $response->getResult();
    }

    /**
     * Callback function from form submitted
     *
     * @param array $continuationDetail
     * @param array $formData
     */
    protected function updateContinuation($continuationDetail, $formData)
    {
        $params = [
            'id' => $continuationDetail['id'],
            'version' => $continuationDetail['version'],
            'received' => $formData['fields']['received'],
        ];

        if (isset($formData['fields']['checklistStatus'])) {
            $params['status'] = $formData['fields']['checklistStatus'];
        }
        if (isset($formData['fields']['totalVehicleAuthorisation'])) {
            $params['totAuthVehicles'] = $formData['fields']['totalVehicleAuthorisation'];
        }
        if (isset($formData['fields']['numberOfDiscs'])) {
            $params['totPsvDiscs'] = $formData['fields']['numberOfDiscs'];
        }
        if (isset($formData['fields']['numberOfCommunityLicences'])) {
            $params['totCommunityLicences'] = $formData['fields']['numberOfCommunityLicences'];
        }

        $response = $this->handleCommand(\Dvsa\Olcs\Transfer\Command\ContinuationDetail\Update::create($params));
        if (!$response->isOk()) {
            throw new \RuntimeException('Error updating ContinuationDetail');
        }
    }

    /**
     * Continue a Licence
     *
     * @param array $licence Licence data
     * @param \Common\Form\Form $form
     * @return bool
     */
    protected function continueLicence(array $licence, $form)
    {
        $response = $this->handleCommand(
            \Dvsa\Olcs\Transfer\Command\Licence\ContinueLicence::create(
                ['id' => $licence['id'], 'version' => $licence['version']]
            )
        );
        if ($response->isClientError()) {
            $errors = ContinuationMapper::mapFromErrors($form, $response->getResult()['messages']);
            if ($errors) {
                foreach ($errors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }
        }
        if ($response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('Unknown error');
        }
        return $response->isOk();
    }

    /**
     * Populate the values of the form
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail Entity data
     */
    protected function populateFormDefaultValues($form, $continuationDetail, $numNotCeasedDiscs)
    {
        $licence = $continuationDetail['licence'];
        $data = array(
            'fields' => [
                'received' => $continuationDetail['received'],
                'checklistStatus' => $continuationDetail['status']['id'],
                'totalVehicleAuthorisation' => $continuationDetail['totAuthVehicles'],
                'numberOfCommunityLicences' => $continuationDetail['totCommunityLicences'],
                'numberOfDiscs' => $continuationDetail['totPsvDiscs'],
            ]
        );
        // if values not in continuationDetails then get from licence
        if ($data['fields']['totalVehicleAuthorisation'] == null) {
            $data['fields']['totalVehicleAuthorisation'] = $licence['totAuthVehicles'];
        }
        if ($data['fields']['numberOfCommunityLicences'] == null) {
            $data['fields']['numberOfCommunityLicences'] = $licence['totCommunityLicences'];
        }
        if ($data['fields']['numberOfDiscs'] == null) {
            if ($licence['goodsOrPsv']['id'] === LicenceEntityService::LICENCE_CATEGORY_PSV) {
                $data['fields']['numberOfDiscs'] = $numNotCeasedDiscs;
            }
        }

        $form->populateValues($data);
    }

    /**
     * Alter the update continuation form dependant on licence and continuation details
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail Entity data
     */
    protected function alterForm($form, $continuationDetail, $hasOutstandingContinuationFee)
    {
        $postData = $this->getRequest()->getPost();

        $this->alterFormReceived($form, $continuationDetail);
        $this->alterFormChecklistStatus($form, $continuationDetail);
        $this->alterFormTotalVehicleAuthorisation($form, $continuationDetail);
        $this->alterFormNumberOfDiscs($form, $continuationDetail, $postData);
        $this->alterFormNumberOfCommunityLicences($form, $continuationDetail, $postData);

        $this->alterFormActions($form, $hasOutstandingContinuationFee, $continuationDetail);

        if ($hasOutstandingContinuationFee) {
            $form->get('fields')->get('message')
                ->setValue('The licence cannot be continued yet because the continuation fee is still outstanding');
        } else {
             $this->getServiceLocator()->get('Helper\Form')->remove($form, 'fields->messages');
        }
    }

    /**
     * Alter form action buttons
     *
     * @param \Zend\Form\Form $form
     * @param bool            $hasOutstandingContinuationFee
     * @param array           $continuationDetail Entity data
     */
    public function alterFormActions($form, $hasOutstandingContinuationFee, $continuationDetail)
    {
        if ($hasOutstandingContinuationFee
            || $continuationDetail['status']['id'] === ContinuationDetailEntityService::STATUS_COMPLETE
            ) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'form-actions->continueLicence');
        }
    }

    /**
     * Only enable the Received element for certain continuation statuses
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail
     */
    protected function alterFormReceived($form, $continuationDetail)
    {
        if ($continuationDetail['status']['id'] === ContinuationDetailEntityService::STATUS_PRINTED
            || ($continuationDetail['status']['id'] !== ContinuationDetailEntityService::STATUS_PRINTED
            && $continuationDetail['received'] === 'N')) {
            // Enabled by default
        } else {
            $this->getServiceLocator()->get('Helper\Form')->disableElement($form, 'fields->received');
        }
    }

    /**
     * Get the continuation details status where things are enabled/active
     *
     * @return array
     */
    protected function getAllowedContinuationStatuses()
    {
        return [
            ContinuationDetailEntityService::STATUS_ACCEPTABLE,
            ContinuationDetailEntityService::STATUS_UNACCEPTABLE,
            ContinuationDetailEntityService::STATUS_PRINTED
        ];
    }

    /**
     * Is status one of the allowed/enabled/active continuation detail statuses
     *
     * @param string $status
     *
     * @return bool
     */
    protected function isAllowedContinuationStatuses($status)
    {
        return in_array($status, $this->getAllowedContinuationStatuses());
    }

    /**
     * Only enable the ChecklistStatus element for certain continuation statuses
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail
     */
    protected function alterFormChecklistStatus($form, $continuationDetail)
    {
        $valueOptions = $form->get('fields')->get('checklistStatus')->getValueOptions();

        if ($this->isAllowedContinuationStatuses($continuationDetail['status']['id'])) {
            if ($continuationDetail['received'] === 'N') {
                $this->getServiceLocator()->get('Helper\Form')->disableElement($form, 'fields->checklistStatus');
            }
            // remove status that we aren't allowed to set to
            $allowedStatuses = $this->getAllowedContinuationStatuses();
            foreach (array_keys($valueOptions) as $key) {
                if (!in_array($key, $allowedStatuses)) {
                    unset($valueOptions[$key]);
                }
            }
        } else {
            $this->getServiceLocator()->get('Helper\Form')->disableElement($form, 'fields->checklistStatus');
            /* @var $e \Zend\Form\Element */
            $e = $form->get('fields')->get('checklistStatus');
            // force element to always disabled, eg JS will not re-enable it
            $e->setAttribute('data-always-disabled', 'true');
        }

        if (isset($valueOptions[ContinuationDetailEntityService::STATUS_PRINTED])) {
            $valueOptions[ContinuationDetailEntityService::STATUS_PRINTED] .= ' (not continued)';
        }
        $form->get('fields')->get('checklistStatus')->setValueOptions($valueOptions);
    }

    /**
     * Only show the TotalVehicleAuthorisation element for certain licence types
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail
     */
    protected function alterFormTotalVehicleAuthorisation($form, $continuationDetail)
    {
        $licence = $continuationDetail['licence'];
        if ($licence['goodsOrPsv']['id'] === LicenceEntityService::LICENCE_CATEGORY_PSV
            && ($licence['licenceType']['id'] === LicenceEntityService::LICENCE_TYPE_RESTRICTED
            || $licence['licenceType']['id'] === LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            || $licence['licenceType']['id'] === LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL)) {
            // Displayed by default
            if (!$this->isAllowedContinuationStatuses($continuationDetail['status']['id'])) {
                $this->getServiceLocator()->get('Helper\Form')
                    ->disableElement($form, 'fields->totalVehicleAuthorisation');
            }
        } else {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'fields->totalVehicleAuthorisation');
        }
    }

    /**
     * Only show the NumberOfDiscs element for certain licence types
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail
     * @param array           $postData
     */
    protected function alterFormNumberOfDiscs($form, $continuationDetail, $postData)
    {
        $licence = $continuationDetail['licence'];
        if ($licence['goodsOrPsv']['id'] === LicenceEntityService::LICENCE_CATEGORY_PSV
            && ($licence['licenceType']['id'] === LicenceEntityService::LICENCE_TYPE_RESTRICTED
            || $licence['licenceType']['id'] === LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            || $licence['licenceType']['id'] === LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL)) {
            // Displayed by default
            $totalVehicles = $licence['totAuthVehicles'];
            if (isset($postData['fields']['totalVehicleAuthorisation'])) {
                $totalVehicles = $postData['fields']['totalVehicleAuthorisation'];
            }

            if ($this->isAllowedContinuationStatuses($continuationDetail['status']['id'])) {
                $this->getServiceLocator()->get('Helper\Form')->attachValidator(
                    $form,
                    'fields->numberOfDiscs',
                    new \Zend\Validator\LessThan(
                        [
                        'max' => $totalVehicles,
                        'inclusive' => true,
                        'translator' => $this->getServiceLocator()->get('Translator'),
                        'message' => 'update-continuation.validation.total-auth-vehicles'
                        ]
                    )
                );
            } else {
                $this->getServiceLocator()->get('Helper\Form')->disableElement($form, 'fields->numberOfDiscs');
            }
        } else {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'fields->numberOfDiscs');
        }
    }

    /**
     * Should the Community Licences input element be displayed
     *
     * @param array $licence Entity data
     *
     * @return bool
     */
    protected function displayCommunityLicenceElement($licence)
    {
        $displayFor = [
            LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE .'-'.
                LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            LicenceEntityService::LICENCE_CATEGORY_PSV .'-'. LicenceEntityService::LICENCE_TYPE_RESTRICTED,
            LicenceEntityService::LICENCE_CATEGORY_PSV .'-'. LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL,
        ];
        $type = $licence['goodsOrPsv']['id'] .'-'. $licence['licenceType']['id'];

        return (in_array($type, $displayFor));
    }

    /**
     * Only show the NumberOfCommunityLicences element for certain licence types
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail
     * @param array           $postData
     */
    protected function alterFormNumberOfCommunityLicences($form, $continuationDetail, $postData)
    {
        $licence = $continuationDetail['licence'];
        if ($this->displayCommunityLicenceElement($licence)) {
            // Displayed by default
            $totalVehicles = $licence['totAuthVehicles'];
            if ($licence['goodsOrPsv']['id'] === LicenceEntityService::LICENCE_CATEGORY_PSV &&
                isset($postData['fields']['totalVehicleAuthorisation'])) {
                $totalVehicles = $postData['fields']['totalVehicleAuthorisation'];
            }

            if ($this->isAllowedContinuationStatuses($continuationDetail['status']['id'])) {
                $this->getServiceLocator()->get('Helper\Form')->attachValidator(
                    $form,
                    'fields->numberOfCommunityLicences',
                    new \Zend\Validator\LessThan(
                        [
                        'max' => $totalVehicles,
                        'inclusive' => true,
                        'translator' => $this->getServiceLocator()->get('Translator'),
                        'message' => 'update-continuation.validation.total-auth-vehicles'
                        ]
                    )
                );
            } else {
                $this->getServiceLocator()->get('Helper\Form')
                    ->disableElement($form, 'fields->numberOfCommunityLicences');
            }
        } else {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'fields->numberOfCommunityLicences');
        }
    }
}

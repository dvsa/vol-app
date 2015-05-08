<?php

/**
 * Continuation Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ContinuationDetailEntityService;
use Common\BusinessService\Service\CreateSeparatorSheet;

/**
 * Continuation Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationController extends AbstractController
{
    /**
     *
     */
    public function updateContinuationAction()
    {
        $licenceId = (int) $this->params()->fromRoute('licence', null);
        $result = $this->getServiceLocator()->get('Entity\ContinuationDetail')
            ->getContinuationMarker($licenceId);
        if ($result['Count'] !== 1) {
            // If viewing this action there should always be one, if not then something has gone wrong
            return $this->notFoundAction();
        }
        $continuationDetail = $result['Results'][0];

        $this->pageLayout = null;
        /* @var $form \Zend\Form\Form */
        $form = $this->getForm('update-continuation');
        $this->alterForm($form, $continuationDetail);
        $this->populateFormDefaultValues($form, $continuationDetail);

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('printSeperator')) {
                //Generates a separator sheet in the same way as the Scanning Page in the Admin section
                $params = [
                    'categoryId' => CreateSeparatorSheet::CATEGORY_LICENCE,
                    'subCategoryId' => CreateSeparatorSheet::SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS,
                    'entityIdentifier' => $continuationDetail['licence']['licNo'],
                    'descriptionId' => CreateSeparatorSheet::DESCRIPTION_CHECKLIST,
                ];

                $this->getServiceLocator()->get('BusinessServiceManager')->get('CreateSeparatorSheet')
                    ->process($params);

                $this->addSuccessMessage('update-continuation.separator-sheet');

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }

            $this->formPost($form);

            if ($form->isValid()) {
                if ($this->isButtonPressed('submit')) {
                    $this->updateContinuation($continuationDetail, $form->getData());
                    $this->addSuccessMessage('update-continuation.saved');
                }

                if ($this->isButtonPressed('continueLicence')) {
                    // @todo Continue the licence (to be done in another user story)
                    $this->updateContinuation($continuationDetail, $form->getData());
                    $this->addSuccessMessage('update-continuation.success');
                }

                return $this->redirectToRouteAjax('licence', array('licence' => $licenceId));
            }
        }
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form-with-fm');

        $this->getServiceLocator()->get('Script')->loadFile('forms/update-continuation');

        return $this->renderView($view, 'Licence continuation');
    }

    /**
     * Callback function from form submitted
     *
     * @param array $continuationDetail
     * @param array $formData
     */
    protected function updateContinuation($continuationDetail, $formData)
    {
        $data = [
            'data' => [
                'id' => $continuationDetail['id'],
                'version' => $continuationDetail['version'],
                'received' => $formData['fields']['received'],
            ]
        ];
        if (isset($formData['fields']['checklistStatus'])) {
            $data['data']['status'] = $formData['fields']['checklistStatus'];
        }
        if (isset($formData['fields']['totalVehicleAuthorisation'])) {
            $data['data']['totAuthVehicles'] = $formData['fields']['totalVehicleAuthorisation'];
        }
        if (isset($formData['fields']['numberOfDiscs'])) {
            $data['data']['totPsvDiscs'] = $formData['fields']['numberOfDiscs'];
        }
        if (isset($formData['fields']['numberOfCommunityLicences'])) {
            $data['data']['totCommunityLicences'] = $formData['fields']['numberOfCommunityLicences'];
        }

        $this->getServiceLocator()->get('BusinessServiceManager')->get('Lva\UpdateContinuationDetail')
            ->process($data);
    }


    /**
     * Populate the values of the form
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail Entity data
     */
    protected function populateFormDefaultValues($form, $continuationDetail)
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
            if ($licence['goodsOrPsv'] === LicenceEntityService::LICENCE_CATEGORY_PSV) {
                $data['fields']['numberOfDiscs'] =
                    $this->getServiceLocator()->get('Entity\PsvDisc')->getNotCeasedDiscs($licence['id'])['Count'];
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
    protected function alterForm($form, $continuationDetail)
    {
        $postData = $this->getRequest()->getPost();

        $this->alterFormReceived($form, $continuationDetail);
        $this->alterFormChecklistStatus($form, $continuationDetail);
        $this->alterFormTotalVehicleAuthorisation($form, $continuationDetail['licence']);
        $this->alterFormNumberOfDiscs($form, $continuationDetail['licence'], $postData);
        $this->alterFormNumberOfCommunityLicences($form, $continuationDetail['licence'], $postData);

        $result = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingContinuationFee($continuationDetail['licenceId']);
        $hasOutstandingContinuationFee = $result['Count'] > 0;

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
     * Only enable the ChecklistStatus element for certain continuation statuses
     *
     * @param \Zend\Form\Form $form
     * @param array           $continuationDetail
     */
    protected function alterFormChecklistStatus($form, $continuationDetail)
    {
        $valueOptions = $form->get('fields')->get('checklistStatus')->getValueOptions();

        if ($continuationDetail['status']['id'] === ContinuationDetailEntityService::STATUS_PRINTED
            || $continuationDetail['status']['id'] === ContinuationDetailEntityService::STATUS_ACCEPTABLE
            || $continuationDetail['status']['id'] === ContinuationDetailEntityService::STATUS_UNACCEPTABLE) {
            $allowedStatuses = [
                ContinuationDetailEntityService::STATUS_ACCEPTABLE,
                ContinuationDetailEntityService::STATUS_UNACCEPTABLE,
                ContinuationDetailEntityService::STATUS_PRINTED
            ];
            // remove status that we aren't allowed to set to
            foreach (array_keys($valueOptions) as $key) {
                if (!in_array($key, $allowedStatuses)) {
                    unset($valueOptions[$key]);
                }
            }
        } else {
            $this->getServiceLocator()->get('Helper\Form')->disableElement($form, 'fields->checklistStatus');
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
     * @param array           $licence
     */
    protected function alterFormTotalVehicleAuthorisation($form, $licence)
    {
        if ($licence['goodsOrPsv'] === LicenceEntityService::LICENCE_CATEGORY_PSV
            && ($licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_RESTRICTED
            || $licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            || $licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL)) {
            // Displayed by default
        } else {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'fields->totalVehicleAuthorisation');
        }
    }

    /**
     * Only show the NumberOfDiscs element for certain licence types
     *
     * @param \Zend\Form\Form $form
     * @param array           $licence
     * @param array           $postData
     */
    protected function alterFormNumberOfDiscs($form, $licence, $postData)
    {
        if ($licence['goodsOrPsv'] === LicenceEntityService::LICENCE_CATEGORY_PSV
            && ($licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_RESTRICTED
            || $licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL
            || $licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL)) {
            // Displayed by default
            $totalVehicles = $licence['totAuthVehicles'];
            if (isset($postData['fields']['totalVehicleAuthorisation'])) {
                $totalVehicles = $postData['fields']['totalVehicleAuthorisation'];
            }

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
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'fields->numberOfDiscs');
        }
    }

    /**
     * Only show the NumberOfCommunityLicences element for certain licence types
     *
     * @param \Zend\Form\Form $form
     * @param array           $licence
     * @param array           $postData
     */
    protected function alterFormNumberOfCommunityLicences($form, $licence, $postData)
    {
        if ($licence['goodsOrPsv'] === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE ||
            ($licence['goodsOrPsv'] === LicenceEntityService::LICENCE_CATEGORY_PSV
            && ($licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_RESTRICTED
            || $licence['licenceType'] === LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL))) {
            // Displayed by default
            $totalVehicles = $licence['totAuthVehicles'];
            if ($licence['goodsOrPsv'] === LicenceEntityService::LICENCE_CATEGORY_PSV &&
                isset($postData['fields']['totalVehicleAuthorisation'])) {
                $totalVehicles = $postData['fields']['totalVehicleAuthorisation'];
            }

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
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'fields->numberOfCommunityLicences');
        }
    }
}

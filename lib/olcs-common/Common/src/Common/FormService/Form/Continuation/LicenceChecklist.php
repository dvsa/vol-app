<?php

namespace Common\FormService\Form\Continuation;

use Common\Form\Form;
use Common\Form\Model\Form\Continuation\LicenceChecklist as LicenceChecklistForm;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\UrlHelperService;

/**
 * Continuation licence checklist form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceChecklist
{
    protected $checklistCheckboxes = [
        'typeOfLicence',
        'businessType',
        'businessDetails',
        'addresses',
        'people',
        'operatingCentres',
        'transportManagers',
        'vehicles',
        'safety',
        'users'
    ];


    public function __construct(protected FormHelperService $formHelper, private UrlHelperService $urlHelper)
    {
    }

    /**
     * Get form
     *
     * @param array $data continuation detail data
     *
     * @return Form
     */
    public function getForm($data)
    {
        $form = $this->formHelper->createForm(LicenceChecklistForm::class);

        $this->alterForm($form, $data);

        return $form;
    }

    /**
     * Alter form
     *
     * @param Form  $form form
     * @param array $data data
     */
    protected function alterForm($form, $data): void
    {
        $orgData = $data['licence']['organisation'];
        $this->alterPeopleSection($form, $orgData['type']['id']);
        $this->alterOperatingCentresSection($form, $data['licence']['vehicleType']['id']);
        $this->alterBackUrl($form, $data['licence']['id']);
        $this->alterContinueButton($form, $data);
        $this->alterAllSections($form, $data['sections']);
    }

    /**
     * Alter people section
     *
     * @param Form   $form    form
     * @param string $orgType organisation type
     */
    protected function alterPeopleSection($form, $orgType): void
    {
        $dataFieldset = $form->get('data');
        $peopleCheckbox = $dataFieldset->get('peopleCheckbox');
        $peopleCheckbox->setLabel(
            $peopleCheckbox->getLabel() . $orgType
        );
        $peopleCheckbox->setOption('not_checked_message', $peopleCheckbox->getOption('not_checked_message') . $orgType);
    }

    /**
     * Alter operating centres section
     *
     * @param Form   $form        form
     * @param string $vehicleType vehicle type
     */
    protected function alterOperatingCentresSection($form, $vehicleType): void
    {
        if ($vehicleType == RefData::APP_VEHICLE_TYPE_LGV) {
            $dataFieldset = $form->get('data');
            $operatingCentresCheckbox = $dataFieldset->get('operatingCentresCheckbox');
            $operatingCentresCheckbox->setLabel(
                $operatingCentresCheckbox->getLabel() . '.lgv'
            );
            $operatingCentresCheckbox->setOption(
                'not_checked_message',
                $operatingCentresCheckbox->getOption('not_checked_message') . '.lgv'
            );
        }
    }

    /**
     * Alter back url
     *
     * @param Form $form      form
     * @param int  $licenceId licence id
     */
    protected function alterBackUrl($form, $licenceId): void
    {
        $backButton = $form->get('licenceChecklistConfirmation')->get('noContent')->get('backToLicence');
        $backButton->setValue(
            $this->urlHelper->fromRoute(
                'lva-licence',
                ['licence' => $licenceId]
            )
        );
    }

    /**
     * Alter all sections
     *
     * @param Form  $form     form
     * @param array $sections sections
     */
    protected function alterAllSections($form, $sections): void
    {
        $key = array_search('vehiclesPsv', $sections, true);
        if ($key !== false) {
            $sections[$key] = 'vehicles';
        }

        foreach ($this->checklistCheckboxes as $checkbox) {
            if (!in_array($checkbox, $sections)) {
                $this->formHelper->remove($form, 'data->' . $checkbox . 'Checkbox');
            }
        }
    }

    /**
     * Alter continue button
     *
     * @param Form  $form form
     * @param array $data data
     */
    protected function alterContinueButton($form, $data): void
    {
        $licenceData = $data['licence'];
        if (
            $licenceData['licenceType']['id'] === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
            && $licenceData['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV
        ) {
            $form->get('licenceChecklistConfirmation')
                ->get('yesContent')
                ->get('submit')
                ->setLabel('continuations.checklist.confirmation.yes-button-declaration');
            return;
        }

        if ($data['hasConditionsUndertakings']) {
            $form->get('licenceChecklistConfirmation')
                ->get('yesContent')
                ->get('submit')
                ->setLabel('continuations.checklist.confirmation.yes-button-conditions-undertakings');
        }
    }
}

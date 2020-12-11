<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Form\Elements\Types\Radio;
use Common\View\Helper\Panel;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard as SwitchBoardForm;
use Olcs\Session\LicenceVehicleManagement;

class SwitchBoardController extends AbstractVehicleController
{
    protected $formConfig = [
        'default' => [
            'switchBoardForm' => [
                'formClass' => SwitchBoardForm::class
            ]
        ]
    ];

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->session->getManager()->getStorage()->clear(LicenceVehicleManagement::SESSION_NAME);

        $view = $this->genericView();
        $view->setVariables($this->getViewVariables());

        return $view;
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     * @throws \Exception
     */
    public function decisionAction()
    {
        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        if (!$this->form->isValid()) {
            return $this->indexAction();
        }

        $selectedOption = $formData[SwitchBoardForm::FIELD_OPTIONS_FIELDSET_NAME]
            [SwitchBoardForm::FIELD_OPTIONS_NAME]
            ?? '';

        if (!$this->optionExistsInForm($selectedOption)) {
            throw new \Exception("Option is not valid '$selectedOption'");
        }

        switch ($selectedOption) {
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD:
                return $this->nextStep('licence/vehicle/add/GET');
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE:
                return $this->nextStep('licence/vehicle/remove/GET');
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT:
                return $this->nextStep('licence/vehicle/reprint/GET');
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER:
                return $this->nextStep('licence/vehicle/transfer/GET');
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW:
                return $this->nextStep('licence/vehicle/list/GET');
            default:
                throw new \Exception('Unexpected value');
        }
    }

    /**
     * @param \Common\Form\Form $form
     * @return \Common\Form\Form
     */
    public function alterForm($form)
    {
        $form = parent::alterForm($form);

        /** @var Radio $radioFieldOptions */
        $radioFieldOptions = $form
            ->get(SwitchBoardForm::FIELD_OPTIONS_FIELDSET_NAME)
            ->get(SwitchBoardForm::FIELD_OPTIONS_NAME);

        $licence = $this->data['licence'];

        if (!$licence['isMlh']) {
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER);
        }

        if ($licence['activeVehicleCount'] === 0) {
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE);
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT);
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER);

            if ($licence['totalVehicleCount'] === 0) {
                $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW);
            } else {
                $valueOptions = $radioFieldOptions->getValueOptions();
                $valueOptions['view']['label'] = "licence.vehicle.switchboard.form.view.label-removed";
                $radioFieldOptions->setValueOptions($valueOptions);
            }
        }

        return $form;
    }

    /**
     * Checks to see if the specified option is present on the altered form.
     *
     * @param string $option
     * @return bool
     */
    protected function optionExistsInForm(string $option): bool
    {
        /** @var Radio $radioFieldOptions */
        $radioFieldOptions = $this->form
            ->get(SwitchBoardForm::FIELD_OPTIONS_FIELDSET_NAME)
            ->get(SwitchBoardForm::FIELD_OPTIONS_NAME);

        return array_key_exists($option, $radioFieldOptions->getValueOptions());
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        $viewVariables = [
            'title' => 'licence.vehicle.switchboard.header',
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'form' => $this->form,
            'backLink' => $this->getLink('lva-licence')
        ];

        $successMessages = $this->getFlashMessenger()->getMessages('success');
        if (!empty($successMessages)) {
            $viewVariables['title'] = 'licence.vehicle.switchboard.header.after-journey';
            $viewVariables['panel'] = [
                'title' => $successMessages[0],
                'body' => $successMessages[1] ?? '',
                'theme' => Panel::TYPE_SUCCESS,
            ];
        }

        return $viewVariables;
    }
}

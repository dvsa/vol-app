<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Form\Form;
use Common\Service\Cqrs\Exception\NotFoundException;
use Exception;
use Olcs\Form\Model\Form\Vehicle\AddVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\ConfirmVehicle;
use Zend\Mvc\MvcEvent;

class AddVehicleSearchController extends AbstractVehicleController
{
    use AddVehicleTrait;

    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => ConfirmVehicle::class,
            ],
            'vehicleSearchForm' => [
                'formClass' => AddVehicleSearch::class,
            ]
        ]
    ];

    protected $pageTemplate = 'pages/licence/vehicle/add';

    const SEARCH_TITLE = 'licence.vehicle.add.search.title';
    const RESULTS_TITLE = 'licence.vehicle.add.result.title';

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $vehicleData = $this->session->getVehicleData();

        if ($vehicleData) {
            $this->form->setData([
                'vehicle-search' => [
                    'search-value' => $vehicleData['registrationNumber']
                ]
            ]);
            $this->alterConfirmationForm();
        }

        return $this->renderView(
            $this->createViewParametersForConfirmation($vehicleData)
        );
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function postAction()
    {
        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        $vrm = $formData['vehicle-search'][AbstractInputSearch::ELEMENT_INPUT_NAME];

        if ($this->form->isValid()) {
            $vehicleData = $this->getVehicleData($vrm);
            $this->alterConfirmationForm();

            if (!empty($vehicleData)) {
                $this->session->setVehicleData($vehicleData);
            }
        }

        return $this->renderView(
            $this->createViewParametersForConfirmation($vehicleData, $vrm)
        );
    }

    /**
     * @return \Zend\Http\Response
     */
    public function clearAction()
    {
        $this->session->destroy();
        return $this->nextStep('licence/vehicle/add/GET');
    }

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function confirmationAction()
    {
        // Redirect to add action if vehicleData is not in session.
        if (!$this->session->hasVehicleData()) {
            $this->hlpFlashMsgr->addErrorMessage('LicenceVehicleManagement does not contain vehicleData');
            return $this->nextStep('licence/vehicle/add/GET');
        }

        $vehicleData = $this->session->getVehicleData();

        if (empty($vehicleData)) {
            $this->hlpFlashMsgr->addErrorMessage("licence.vehicle.add.unable-to-add");
            return $this->nextStep('licence/vehicle/add/GET');
        }

        $response = $this->handleCommand(
            $this->generateCreateVehicleCommand(
                $vehicleData['registrationNumber'],
                $vehicleData['make'],
                false,
                $vehicleData['revenueWeight'] ?? 0
            )
        );

        if ($response->isOk()) {
            $this->hlpFlashMsgr->addSuccessMessage(
                $this->translator->translateReplace(
                    'licence.vehicle.add.success',
                    [$vehicleData['registrationNumber']]
                )
            );
            return $this->nextStep('licence/vehicle/GET');
        }

        // Is the VRM already defined on a licence?
        if (isset($response->getResult()['messages']['VE-VRM-2'])) {
            return $this->nextStep('licence/vehicle/add/duplicate-confirmation/GET');
        }

        $message = array_values($response->getResult()['messages']['vrm'])[0];
        $this->hlpFlashMsgr->addErrorMessage($message);

        return $this->nextStep('licence/vehicle/add/GET');
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => static::SEARCH_TITLE,
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/GET'),
            'bottomLink' => $this->getLink('licence/vehicle/add/clear'),
            'bottomText' => 'licence.vehicle.clear-search'
        ];
    }

    /**
     * Alter the confirmation route to add the form action and set the vrm
     */
    private function alterConfirmationForm(): void
    {
        /** @var Form $form */
        $form = $this->forms['confirmationForm'];

        $form->setAttribute(
            'action',
            $this->getLink('licence/vehicle/add/confirmation')
        );
    }

    /**
     * @param $vehicleData
     * @param null $searchedVrm
     * @return array
     */
    private function createViewParametersForConfirmation($vehicleData, $searchedVrm = null): array
    {
        return array_merge(
            $this->getViewVariables(),
            [
                'vehicleData' => $vehicleData ?? null,
                'vrm' => $searchedVrm,
                'title' => $vehicleData ? static::RESULTS_TITLE : static::SEARCH_TITLE,
                'confirmationForm' => $this->forms['confirmationForm']
            ]
        );
    }

    /**
     * @param string $message
     * @param string $type
     */
    protected function setFormErrorMessage(string $message, string $type): void
    {
        $this->form->get('vehicle-search')->setMessages([
            AbstractInputSearch::ELEMENT_INPUT_NAME => [
                $type => $this->translator->translate($message)
            ]
        ]);
    }

    /**
     * @param $vrm
     * @return array|null
     */
    protected function getVehicleData($vrm): ?array
    {
        try {
            $vehicleData = $this->fetchDvlaVehicleData($vrm);
        } catch (NotFoundException $exception) {
            $this->setFormErrorMessage('licence.vehicle.add.search.vrm-not-found', 'vrm_not_found');
        } catch (Exception $exception) {
            $this->hlpFlashMsgr->addErrorMessage($this->translator->translate('licence.vehicle.add.search.query-error'));
        }
        return $vehicleData ?? null;
    }
}

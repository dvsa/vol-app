<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Form\Form;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Licence\CreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvVehicle;
use Exception;
use Olcs\Form\Model\Form\Vehicle\AddVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\ConfirmVehicle;
use Zend\Mvc\MvcEvent;

class AddVehicleSearchController extends AbstractVehicleController
{
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

    /**
     * @var TranslationHelperService
     */
    private $translator;

    const SEARCH_TITLE = 'licence.vehicle.add.search.title';
    const RESULTS_TITLE = 'licence.vehicle.add.result.title';

    public function onDispatch(MvcEvent $e)
    {
        $this->translator = $this->getServiceLocator()->get('Helper\Translation');
        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $params = $this->getViewVariables();

        return $this->renderView($params);
    }

    public function postAction()
    {
        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        $vrm = $formData['vehicle-search'][AbstractInputSearch::ELEMENT_INPUT_NAME];

        if ($this->form->isValid()) {
            $vehicleData = $this->getVehicleData($vrm);
        }

        $params = array_merge(
            $this->getViewVariables(),
            [
                'vehicleData' => $vehicleData ?? null,
                'vrm' => $vrm,
                'title' => $vehicleData ? static::RESULTS_TITLE : static::SEARCH_TITLE,
                'confirmationForm' => $this->forms['confirmationForm']
            ]
        );

        return $this->renderView($params);
    }

    public function confirmationAction()
    {
        $vrm = $this->getRequest()->getPost('vrm');
        $vehicleData = $this->getVehicleData($vrm);

        if (empty($vehicleData)) {
            $this->hlpFlashMsgr->addErrorMessage("licence.vehicle.add.unable-to-add");
            return $this->redirect()->toRoute('licence/vehicle/add/GET', [], [], true);
        }

        if ($this->isGoods()) {
            $command = CreateGoodsVehicle::create([
                'id' => $this->licenceId,
                'vrm' => $vehicleData['registrationNumber'],
                'platedWeight' => $vehicleData['revenueWeight'],
                'makeModel' => $vehicleData['make']
            ]);
        } else {
            $command = CreatePsvVehicle::create([
                'id' => $this->licenceId,
                'vrm' => $vehicleData['registrationNumber'],
                'makeModel' => $vehicleData['make']
            ]);
        }

        $response = $this->handleCommand($command);

        if ($response->isOk()) {
            $this->hlpFlashMsgr->addSuccessMessage("Vehicle {$vrm} has been added");
            //TODO: Update this route to the switchboard once it's available
            return $this->redirect()->toRoute('licence/vehicle/add/GET', [], [], true);
        }

        $message = array_values($response->getResult()['messages']['vrm'])[0];
        $this->hlpFlashMsgr->addErrorMessage($message);

        return $this->redirect()->toRoute('licence/vehicle/add/GET', [], [], true);
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
            'backLink' => $this->url()->fromRoute('lva-licence', [], [], true),
            'bottomLink' => $this->url()->fromRoute('licence/vehicle/add/GET', [], [], true),
            'bottomText' => 'licence.vehicle.add.bottom-text'
        ];
    }

    /**
     * Alter the confirmation route to add the form action and set the vrm
     *
     * @param string $vrm
     */
    private function alterConfirmationForm(string $vrm): void
    {
        /** @var Form $form */
        $form = $this->forms['confirmationForm'];

        $form->setAttribute(
            'action',
            $this->url()->fromRoute(
                'licence/vehicle/add/confirmation',
                ['licence' => $this->licenceId]
            )
        );

        $form->get('vrm')->setValue($vrm);
    }

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
            $vehicleData = $this->fetchVehicleData($vrm);
            $this->alterConfirmationForm($vrm);
        } catch (NotFoundException $exception) {
            $this->setFormErrorMessage('licence.vehicle.add.search.vrm-not-found', 'vrm_not_found');
        } catch (Exception $exception) {
            $this->hlpFlashMsgr->addErrorMessage($this->translator->translate('licence.vehicle.add.search.query-error'));
        }
        return $vehicleData ?? null;
    }
}

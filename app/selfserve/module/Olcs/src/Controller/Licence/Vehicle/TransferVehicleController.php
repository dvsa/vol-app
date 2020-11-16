<?php
declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Query\Licence\OtherActiveLicences;
use Olcs\Form\Model\Form\Vehicle\Fieldset\VehicleTransferFormActions;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\VehicleTransferForm;
use Zend\Form\Element\Select;
use Zend\View\Model\ViewModel;
use Olcs\Exception\Licence\Vehicle\NoLicencesFoundException;

class TransferVehicleController extends AbstractVehicleController
{
    public const VEHICLE_TRANSFER_LIMIT = 20;
    public const VEHICLE_WARNING_LIMIT = 10;
    public const VEHICLE_SEARCH_FORM_THRESHOLD = 10;
    protected const LICENCE_VEHICLE_TRANSFER_HEADER = 'licence.vehicle.transfer.header';
    protected const LICENCE_VEHICLE_TRANSFER_SEARCH_HEADER = 'licence.vehicle.transfer.search.header';
    protected const ACTION_TRANSFER = 'action';

    protected $formConfig = [
        'default' => [
            'searchForm' => [
                'formClass' => ListVehicleSearch::class
            ],
            'goodsVehicleForm' => [
                'formClass' => VehicleTransferForm::class,
            ]
        ]
    ];

    /**
     * Handles a request from a user to show the transfer vehicles page.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        try {
            $this->alterVehicleForm();
        } catch (NoLicencesFoundException $ex) {
            // If a user has no licences, redirect them to the switchboard.
            return $this->nextStep('licence/vehicle/GET');
        }

        $vehicleTable = $this->createVehicleTable();
        $tableFieldset = $this->form->get('table');
        $tableFieldset->get('table')->setTable($vehicleTable);
        $tableFieldset->get('rows')->setValue(count($vehicleTable->getRows()));

        $view = $this->genericView();
        $view->setVariables($this->getViewVariables());

        if ($vehicleTable->getTotal() > static::VEHICLE_WARNING_LIMIT) {
            $view->setVariable('note', $this->translator->translate('licence.vehicle.transfer.note.more-then-20-vehicles'));
        }

        if ($vehicleTable->getTotal() > static::VEHICLE_SEARCH_FORM_THRESHOLD || $this->isSearchResultsPage()) {
            $this->alterSearchForm();
            $view->setVariable('searchForm', $this->forms['searchForm']);
        }

        return $view;
    }

    /**
     * Handles a request from a user to transfer one or more vehicles to a given licence.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function postAction()
    {
        $input = $this->getRequest()->getPost();
        $action = $input['formActions']['action'] ?? false;
        if ($action !== static::ACTION_TRANSFER) {
            return $this->nextStep('licence/vehicle/GET');
        }

        $selectedVehicles = $input['table']['id'] ?? null;
        $validationErrors = [];
        if (empty($selectedVehicles)) {
            $validationErrors[] = 'licence.vehicle.transfer.error.no-vehicle-selected';
        }
        if (count($selectedVehicles) > static::VEHICLE_TRANSFER_LIMIT) {
            $validationErrors[] = 'licence.vehicle.transfer.error.too-many-vehicles-selected';
        }

        $licence = $input['formActions'][VehicleTransferFormActions::LICENCE_FIELD] ?? null;
        if (empty($licence)) {
            $validationErrors[] = 'licence.vehicle.transfer.error.no-licence-selected';
        }

        if (count($validationErrors) > 0) {
            $this->form->get('formActions')->setMessages($validationErrors);
            return $this->indexAction();
        }

        $this->session->setVrms($selectedVehicles);
        $this->hlpFlashMsgr->addSuccessMessage(
            $this->translator->translate('If this was real it would have sent you to the confirm page')
        );
        return $this->nextStep('licence/vehicle/GET');
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => $this->isSearchResultsPage() ? static::LICENCE_VEHICLE_TRANSFER_SEARCH_HEADER : static::LICENCE_VEHICLE_TRANSFER_HEADER,
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'clearUrl' => $this->getLink('licence/vehicle/transfer/GET'),
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/GET'),
            'bottomContent' => $this->getChooseDifferentActionMarkup()
        ];
    }

    /**
     * @throws NoLicencesFoundException
     */
    protected function alterVehicleForm()
    {
        $this->form->get('formActions')
            ->get('action')
            ->setAttribute('title', 'licence.vehicle.transfer.button.action.title')
            ->setAttribute('value', static::ACTION_TRANSFER)
            ->setLabel('licence.vehicle.transfer.button.action.label');
        $this->form->get('formActions')
            ->get('cancel')
            ->setAttribute('title', 'licence.vehicle.transfer.button.cancel.title');
        $this->setFormLicenceOptions($this->form, $this->licenceId);
    }

    /**
     * Sets the licence options within a form.
     *
     * @param Form $form
     * @param int $licenceId
     * @throws NoLicencesFoundException
     */
    protected function setFormLicenceOptions(Form $form, int $licenceId)
    {
        $otherActiveLicenceOptions = $this->getOtherActiveLicenceOptions($licenceId);
        if (count($otherActiveLicenceOptions) < 1) {
            throw new NoLicencesFoundException();
        }
        $selectFormElement = $form->get('formActions')->get(VehicleTransferFormActions::LICENCE_FIELD);
        $selectFormElement->setValueOptions($otherActiveLicenceOptions);
        if (count($otherActiveLicenceOptions) > 1) {
            $selectFormElement->setEmptyOption("licence.vehicle.transfer.select.licence.empty-option");
        }
    }

    /**
     * Gets options for all other active licences given a licence id.
     *
     * @param int $licenceId
     * @return array<int,string>
     */
    protected function getOtherActiveLicenceOptions(int $licenceId)
    {
        $response = $this->handleQuery(OtherActiveLicences::create(['id' => $licenceId]));
        $otherActiveLicenceOptions = [];
        $result = $response->getResult();
        $otherActiveLicences = $result['otherActiveLicences'] ?? [];
        foreach ($otherActiveLicences as $otherActiveLicence) {
            $otherActiveLicenceOptions[$otherActiveLicence['id']] = $otherActiveLicence['licNo'];
        }
        return $otherActiveLicenceOptions;
    }

    protected function alterSearchForm()
    {
        /** @var Form $form */
        $form = $this->forms['searchForm'];
        $form->get('vehicleSearch')
            ->setOption('legend', 'licence.vehicle.transfer.table.search.legend');

        $formData = $this->getRequest()->getQuery();
        $form->setData($formData);

        if (array_key_exists('vehicleSearch', $formData)) {
            $form->isValid();
        }

        $form->remove('security');
    }
}

<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\OtherActiveLicences;
use Exception;
use Laminas\Form\Element\Select;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use Olcs\DTO\Licence\OtherActiveLicenceListDTO;
use Olcs\Exception\Licence\Vehicle\VehiclesNotFoundWithIdsException;
use Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboard;
use Olcs\Form\Model\Form\Vehicle\View\ViewVehicleSwitchboardFieldset;
use Permits\Data\Mapper\MapperManager;

/**
 * @see ViewVehicleControllerFactory
 */
class ViewVehicleController extends AbstractVehicleController
{
    public const REF_DATA_ATTRIBUTES = ['description', 'displayOrder', 'id', 'olbsKey', 'refDataCategoryId', 'version'];

    protected $formConfig = [
        'default' => [
            'switchboard' => [
                'formClass' => ViewVehicleSwitchboard::class
            ],
        ]
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessenger $flashMessenger
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessenger $flashMessenger
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessenger);
    }

    /**
     * Handles a request from a user to view a single vehicle.
     *
     * @return ViewModel
     * @throws VehiclesNotFoundWithIdsException
     */
    #[\Override]
    public function indexAction()
    {
        $licenceId = (int) $this->params()->fromRoute('licence');
        $vehicleId = (int) $this->params()->fromRoute('vehicle');
        $licenceVehicleData = $this->findOneLicenceVehicleByLicenceAndVehicle($licenceId, $vehicleId);
        $this->prepareForm($this->form, $this->getRequest(), $this->getEvent()->getRouteMatch());
        $view = new ViewModel([
            'licNo' => $this->data['licence']['licNo'],
            'vehicleDetails' => [
                'registrationNumber' => $licenceVehicleData['vehicle']['vrm'],
                'revenueWeight' => $licenceVehicleData['vehicle']['platedWeight'],
                'make' => $licenceVehicleData['vehicle']['makeModel'],
            ],
            'form' => $this->form,
            'manageVehiclesLink' => $this->getLink('lva-licence/vehicles'),
            'backLink' => $this->getLink('licence/vehicle/list/GET'),
        ]);
        $view->setTemplate('/pages/licence/vehicle/view');
        return $view;
    }

    /**
     * Handles a form submission from a user to redirect them to a page that will allow them to perform another action
     * on a vehicle.
     *
     * @return Response|ViewModel
     * @throws VehiclesNotFoundWithIdsException
     */
    public function submitAction()
    {
        $request = $this->getRequest();
        $licenceId = (int) $this->params()->fromRoute('licence');
        $vehicleId = (int) $this->params()->fromRoute('vehicle');
        $licenceVehicleData = $this->findOneLicenceVehicleByLicenceAndVehicle($licenceId, $vehicleId);
        $this->prepareForm($this->form, $request, $this->getEvent()->getRouteMatch());
        if (! $this->form->isValid()) {
            return $this->indexAction();
        }
        $this->session->setVrms([$licenceVehicleData['id']]);
        $radioElement = $this->form->get(ViewVehicleSwitchboard::FIELD_OPTIONS_FIELDSET_NAME);
        assert($radioElement instanceof \Common\Form\Elements\Types\RadioVertical, 'Expected instance of \Common\Form\Elements\Types\RadioVertical');
        $radioOptionsElement = $radioElement->get(ViewVehicleSwitchboardFieldset::ATTR_OPTIONS);
        switch ($radioOptionsElement->getValue()) {
            case ViewVehicleSwitchboardFieldset::RADIO_OPTION_REMOVE:
                return $this->nextStep('licence/vehicle/remove/confirm/GET');
            case ViewVehicleSwitchboardFieldset::RADIO_OPTION_REPRINT:
                return $this->nextStep('licence/vehicle/reprint/confirm/GET');
            case ViewVehicleSwitchboardFieldset::RADIO_OPTION_TRANSFER:
                $selectElement = $radioElement->get(ViewVehicleSwitchboardFieldset::ATTR_TRANSFER_CONTENT);
                assert($selectElement instanceof Select, 'Expected instance of Select');
                if (empty($selectElement->getValue())) {
                    $selectElement->setMessages(['licence.vehicle.view.switchboard.option.transfer.error.is-empty']);
                    return $this->indexAction();
                }
                $destinationLicenceId = (int) $selectElement->getValue();
                $this->session->setDestinationLicenceId($destinationLicenceId);
                return $this->nextStep('licence/vehicle/transfer/confirm/GET');
            default:
                $radioOptionsElement->setMessages(['licence.vehicle.view.switchboard.error.invalid']);
                return $this->indexAction();
        }
    }

    /**
     * Prepares a form.
     */
    protected function prepareForm(Form $form, Request $request, RouteMatch $routeMatch): void
    {
        $input = $request->isPost() ? $request->getPost()->toArray() : [];
        $licenceId = (int) $routeMatch->getParam('licence');

        // Prepare translation option
        $optionsFieldset = $form->get(ViewVehicleSwitchboard::FIELD_OPTIONS_FIELDSET_NAME);
        $otherLicences = $this->getOtherActiveLicenceOptions($licenceId)->getOtherLicences();
        if (empty($otherLicences)) {
            $optionsFieldset->remove(ViewVehicleSwitchboardFieldset::ATTR_TRANSFER_CONTENT);
            $switchboardElement = $optionsFieldset->get(ViewVehicleSwitchboard::FIELD_OPTIONS_NAME);
            assert($switchboardElement instanceof \Common\Form\Elements\Types\Radio, 'Expected instance of \Common\Form\Elements\Types\Radio');
            $switchboardElement->unsetValueOption(ViewVehicleSwitchboardFieldset::RADIO_OPTION_TRANSFER);
        } else {
            $select = $optionsFieldset->get(ViewVehicleSwitchboardFieldset::ATTR_TRANSFER_CONTENT);
            assert($select instanceof Select, 'Expected instance of Select');
            $this->setLicenceSelectValueOptions($select, $otherLicences);
        }

        // Set form data
        $this->form->setData($input);
    }

    /**
     * Sets the value options for a licence select element.
     */
    protected function setLicenceSelectValueOptions(Select $select, array $licences): void
    {
        $select->setValueOptions($this->prepareLicenceSelectValueOptions($licences));
        $select->setEmptyOption("licence.vehicle.view.switchboard.option.transfer.select.empty-option");
        if (count($licences) === 1) {
            $otherActiveLicence = array_values($licences)[0];
            $selectFormElementClass = $select->getAttribute('class');
            $select->setAttribute('class', sprintf('%s govuk-!-display-none', $selectFormElementClass));
            $select->setLabel($this->translationHelper->translateReplace(
                "licence.vehicle.view.switchboard.option.transfer.select.label.singular",
                [$otherActiveLicence->getLicenceNumber()]
            ));
            $select->setValue($otherActiveLicence->getId());
            $select->setEmptyOption(null);
        }
    }

    /**
     * Sets the licence options on a select element.
     *
     * @return array
     */
    protected function prepareLicenceSelectValueOptions(array $otherActiveLicences)
    {
        $valueOptions = [];
        foreach ($otherActiveLicences as $otherActiveLicence) {
            $valueOptions[$otherActiveLicence->getId()] = $otherActiveLicence->getLicenceNumber();
        }
        return $valueOptions;
    }

    /**
     * Gets options for all other active licences given a licence id.
     *
     * @return OtherActiveLicenceListDTO
     */
    protected function getOtherActiveLicenceOptions(int $licenceId): OtherActiveLicenceListDTO
    {
        $query = OtherActiveLicences::create(['id' => $licenceId]);
        $response = $this->handleQuery($query);
        $result = $response->getResult();
        return new OtherActiveLicenceListDTO(is_array($result) ? $result : []);
    }

    /**
     * Finds a licence vehicle by licence id and vehicle id.
     *
     * @return array
     * @throws VehiclesNotFoundWithIdsException
     */
    protected function findOneLicenceVehicleByLicenceAndVehicle(int $licenceId, int $vehicleId): array
    {
        $query = GoodsVehicles::create([
            'id' => $licenceId,
            'vehicleIds' => [$vehicleId],
            'limit' => 10,
            'page' => 1,
            'sort' => 'id',
            'order' => 'desc',
        ]);

        try {
            $queryResult = $this->handleQuery($query);
        } catch (NotFoundException | AccessDeniedException) {
            throw new VehiclesNotFoundWithIdsException([$vehicleId]);
        }

        $result = $queryResult->getResult();

        $licenceVehiclesData = $result['licenceVehicles']['results'] ?? [];

        if (count($licenceVehiclesData) > 1) {
            throw new Exception('Multiple records found were only one expected');
        }

        if (!is_array($licenceVehiclesData) || empty($licenceVehiclesData)) {
            throw new VehiclesNotFoundWithIdsException([$vehicleId]);
        }

        $licenceVehicleData = array_values($licenceVehiclesData)[0];
        if (!is_array($licenceVehicleData) || empty($licenceVehicleData)) {
            throw new VehiclesNotFoundWithIdsException([$vehicleId]);
        }

        $data = array_intersect_key($licenceVehicleData, array_flip(['id', 'olbsKey', 'receivedDate', 'removalDate',
            'removalLetterSeedDate', 'specifiedDate', 'version', 'viAction', 'warningLetterSeedDate',
            'warningLetterSentDate', 'createdOn', 'lastModifiedOn', 'deletedDate',
        ]));

        $data['vehicle'] = array_intersect_key($licenceVehicleData['vehicle'], array_flip(['certificateNo', 'id',
            'makeModel', 'olbsKey', 'platedWeight', 'section26', 'section26Curtail', 'section26Revoked',
            'section26Suspend', 'version', 'viAction', 'vrm', 'createdOn', 'lastModifiedOn', 'deletedDate',
        ]));

        $data['licence'] = array_intersect_key($result, array_flip(['cnsDate', 'correspondenceCd', 'curtailedDate',
            'enforcementArea', 'establishmentCd', 'expiryDate', 'fabsReference', 'feeDate', 'grantedDate', 'id',
            'inForceDate', 'isMaintenanceSuitable', 'licNo', 'olbsKey', 'optOutTmLetter', 'psvDiscsToBePrintedNo',
            'reviewDate', 'revokedDate', 'safetyIns', 'safetyInsTrailers', 'safetyInsVaries', 'safetyInsVehicles',
            'surrenderedDate', 'suspendedDate', 'tachographIns', 'tachographInsName', 'totAuthTrailers',
            'totAuthVehicles', 'totCommunityLicences', 'trafficArea', 'trailersInPossession', 'translateToWelsh',
            'transportConsultantCd', 'version', 'viAction', 'createdOn', 'lastModifiedOn', 'deletedDate', 'niFlag',
            'canReprint', 'canTransfer', 'canExport', 'canPrintVehicle', 'spacesRemaining', 'activeVehicleCount',
            'allVehicleCount'
        ]));

        $data['licence']['goodsOrPsv'] = array_intersect_key($result['goodsOrPsv'], array_flip(static::REF_DATA_ATTRIBUTES));
        $data['licence']['licenceType'] = array_intersect_key($result['licenceType'], array_flip(static::REF_DATA_ATTRIBUTES));
        $data['licence']['status'] = array_intersect_key($result['status'], array_flip(static::REF_DATA_ATTRIBUTES));

        return $data;
    }
}

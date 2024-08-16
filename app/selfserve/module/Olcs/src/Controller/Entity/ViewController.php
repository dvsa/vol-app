<?php

namespace Olcs\Controller\Entity;

use Common\Controller\Lva\AbstractController;
use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query\Search\Licence as SearchLicence;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Session\Container;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Entity View Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ViewController extends AbstractController
{
    public const USER_TYPE_PARTNER = 'partner';
    public const USER_TYPE_ANONYMOUS = 'anonymous';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected TableFactory $tableFactory
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * @var $entity
     */
    private $entity;

    private $userType;

    /**
     * Wrapper method to call appropriate entity action
     *
     * @return \Laminas\View\Model\ConsoleModel|\Laminas\View\Model\ViewModel
     */
    public function detailsAction()
    {
        $this->entity = $this->params()->fromRoute('entity');
        $action = $this->entity . 'Action';

        $this->userType = $this->getUserType();

        if (method_exists($this, $action)) {
            return $this->$action();
        }

        return $this->notFoundAction();
    }

    /**
     * licence action
     *
     * @return \Laminas\View\Model\ConsoleModel|\Laminas\View\Model\ViewModel
     * @throws ResourceNotFoundException
     */
    public function licenceAction()
    {
        $entityId = (int)$this->params()->fromRoute('entityId');

        if ($entityId === 0) {
            throw new ResourceNotFoundException('Licence identifier not provided or invalid');
        }

        // retrieve data
        $query = SearchLicence::create(['id' => $entityId]);
        $response = $this->handleQuery($query);

        // handle response
        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        $result = null;
        if ($response->isOk()) {
            $result = $response->getResult();
        }

        // https://jira.i-env.net/browse/OLCS-14852
        // Cannot view licence that is in one of these statuses
        $cannotViewStatuses = [
            RefData::LICENCE_STATUS_NOT_SUBMITTED,
            RefData::LICENCE_STATUS_UNLICENSED,
            RefData::LICENCE_STATUS_WITHDRAWN,
        ];
        if (in_array($result['status']['id'], $cannotViewStatuses)) {
            return $this->notFoundAction();
        };

        // setup layout and view
        $content = $this->generateContent($result);

        $layout = $this->generateLayout($result['organisation']['name'], $result['licNo']);
        $layout->addChild($content, 'content');

        return $layout;
    }

    /**
     * Set up the layout with title, subtitle and content
     *
     * @param string $title    Title
     * @param string $subtitle SubTitle
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function generateLayout($title = null, $subtitle = null)
    {
        $layout = new \Laminas\View\Model\ViewModel(
            [
                'pageTitle' => $title,
                'pageSubtitle' => $subtitle,
                'userType' => $this->getUserType(),
                'urlBackToSearch' => $this->generateSearchResultsLink()
            ]
        );
        $layout->setTemplate('layouts/entity-view');

        return $layout;
    }

    /**
     * Generate page content
     *
     * @param array $result Api Data
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function generateContent($result)
    {
        $content = new \Laminas\View\Model\ViewModel(
            array_merge(
                [
                    'result' => $result,
                    'soleTraderOrRegisteredCompanyType' => [
                        RefData::ORG_TYPE_REGISTERED_COMPANY,
                        RefData::ORG_TYPE_SOLE_TRADER,
                    ],
                    'registeredCompanyType' => RefData::ORG_TYPE_REGISTERED_COMPANY,
                    'partnershipCompanyType' => RefData::ORG_TYPE_PARTNERSHIP,
                    'otherCompanyType' => RefData::ORG_TYPE_OTHER,
                    'llpCompanyType' => RefData::ORG_TYPE_LLP,
                    'irfoCompanyType' => RefData::ORG_TYPE_IRFO,
                    'soleTraderCompanyType' => RefData::ORG_TYPE_SOLE_TRADER
                 ],
                $this->generateTables($result)
            )
        );
        $template = 'olcs/entity/' . $this->entity . '/' . $this->userType;

        $content->setTemplate($template);
        return $content;
    }

    /**
     * Generates the url as a Query string to go back to
     *
     * @return string
     */
    private function generateSearchResultsLink()
    {
        $searchQueryParams = new Container('searchQuery');

        if (empty($searchQueryParams->routeParams) || empty($searchQueryParams->queryParams)) {
            // return default search link
            return $this->url()->fromRoute(
                'search'
            );
        }

        return $this->url()->fromRoute(
            'search',
            $searchQueryParams->routeParams,
            [
                'query' => $searchQueryParams->queryParams
            ]
        );
    }

    /**
     * Get the user type
     *
     * @return string
     */
    private function getUserType()
    {
        if (!empty($this->userType)) {
            return $this->userType;
        }

        if (
            $this->isGranted(RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN)
            || $this->isGranted(RefData::PERMISSION_SELFSERVE_PARTNER_USER)
        ) {
            $this->userType = self::USER_TYPE_PARTNER;
        } else {
            $this->userType = self::USER_TYPE_ANONYMOUS;
        }

        return $this->userType;
    }
    /**
     * Generate Tables
     *
     * @param array $data Api data
     *
     * @return array
     */
    private function generateTables($data)
    {
        $tables = [];
        $tableService = $this->tableFactory;

        $tables['relatedOperatorLicencesTable'] = $tableService->buildTable(
            'entity-view-related-operator-licences',
            $data['otherLicences'] ?: []
        );

        $tables['transportManagerTable'] = $tableService->buildTable(
            'entity-view-transport-managers',
            $data['transportManagers'] ?: []
        );

        $tables['operatingCentresTable'] = $tableService->buildTable(
            'entity-view-operating-centres-' . $this->userType,
            $data['operatingCentres'] ?: [],
            [],
            false
        );

        $this->alterOperatingCentresTableForLgv($tables['operatingCentresTable'], $data);

        // Using OCs again, just using different data
        $tables['oppositionsTable'] = $tableService->buildTable(
            'entity-view-oppositions-' . $this->userType,
            $data['operatingCentres'] ?: []
        );

        if ($this->userType == self::USER_TYPE_PARTNER) {
            $tables['vehiclesTable'] = $tableService->buildTable(
                'entity-view-vehicles-' . $this->userType,
                $data['vehicles'] ?: []
            );

            $tables['currentApplicationsTable'] = $tableService->buildTable(
                'entity-view-current-applications-' . $this->userType,
                $data['applications'] ?: []
            );

            $tables['conditionsUndertakingsTable'] = $tableService->buildTable(
                'entity-view-conditions-undertakings-' . $this->userType,
                $data['conditionUndertakings'] ?: []
            );
        }

        return $tables;
    }

    /**
     * Alter the operating centres table in accordance with lgv requirements
     */
    private function alterOperatingCentresTableForLgv(TableBuilder $tableBuilder, array $data): void
    {
        $isMixedWithLgv = ($data['vehicleType']['id'] === RefData::APP_VEHICLE_TYPE_MIXED) && ($data['totAuthLgvVehicles'] !== null);

        if ($isMixedWithLgv) {
            $columns = $tableBuilder->getColumns();
            $columns['noOfVehiclesRequired']['title'] = str_replace(
                'vehicles',
                'hgvs',
                $columns['noOfVehiclesRequired']['title']
            );
            $tableBuilder->setColumns($columns);
        }
    }
}

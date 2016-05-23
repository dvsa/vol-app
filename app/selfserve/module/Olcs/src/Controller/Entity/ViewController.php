<?php

namespace Olcs\Controller\Entity;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Search\Licence as SearchLicence;
use Zend\Session\Container;

/**
 * Entity View Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ViewController extends AbstractController
{
    const USER_TYPE_PARTNER = 'partner';
    const USER_TYPE_ANONYMOUS = 'anonymous';

    /**
     * @var $entity
     */
    private $entity;

    private $userType;

    /**
     * Wrapper method to call appropriate entity action
     * @return array
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
     */
    public function licenceAction()
    {
        $entityId = $this->params()->fromRoute('entityId');

        // retrieve data
        $query = SearchLicence::create(['id' => $entityId]);
        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        $result = null;
        if ($response->isOk()) {
            $result = $response->getResult();
        }

        // setup layout and view
        $content = $this->generateContent($result);

        $layout = $this->generateLayout($result['organisation']['name'], $result['licNo']);
        $layout->addChild($content, 'content');

        return $layout;
    }

    /**
     * Set up the layout with title, subtitle and content
     *
     * @param null $title
     * @param null $subtitle
     * @return \Zend\View\Model\ViewModel
     */
    private function generateLayout($title = null, $subtitle = null)
    {
        $layout = new \Zend\View\Model\ViewModel(
            [
                'pageTitle' => $title,
                'pageSubtitle' => $subtitle,
                'userType' => $this->getUserType(),
                'searchResultsLink' => $this->generateSearchResultsLink()
            ]
        );
        $layout->setTemplate('layouts/entity-view');

        return $layout;
    }

    /**
     * Generate page content
     * @param $result
     * @return \Zend\View\Model\ViewModel
     */
    private function generateContent($result)
    {
        $content = new \Zend\View\Model\ViewModel(
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
     * @return string
     */
    private function getUserType()
    {
        if (!empty($this->userType)) {
            return $this->userType;
        }

        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN)
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
     * @param $data
     * @return array
     */
    private function generateTables($data)
    {
        $tables = [];
        $tableService = $this->getServiceLocator()->get('Table');

        $tables['relatedOperatorLicencesTable'] = $tableService->buildTable(
            'entity-view-related-operator-licences',
            $data['otherLicences']
        );

        $tables['transportManagerTable'] = $tableService->buildTable(
            'entity-view-transport-managers',
            $data['transportManagers']
        );

        $tables['operatingCentresTable'] = $tableService->buildTable(
            'entity-view-operating-centres-' . $this->userType,
            $data['operatingCentres']
        );

        // Using OCs again, just using different data
        $tables['oppositionsTable'] = $tableService->buildTable(
            'entity-view-oppositions-' . $this->userType,
            $data['operatingCentres']
        );

        if ($this->userType == self::USER_TYPE_PARTNER) {

            $tables['vehiclesTable'] = $tableService->buildTable(
                'entity-view-vehicles-' . $this->userType,
                $data['vehicles']
            );

            $tables['currentApplicationsTable'] = $tableService->buildTable(
                'entity-view-current-applications-' . $this->userType,
                $data['applications']
            );

            $tables['conditionsUndertakingsTable'] = $tableService->buildTable(
                'entity-view-conditions-undertakings-' . $this->userType,
                $data['conditionUndertakings']
            );
        }

        return $tables;
    }
}

<?php
/**
 * Search Result Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Olcs\Controller\Search;

use Common\Controller\Lva\AbstractController;
use Common\Service\Entity\UserEntityService;
use Dvsa\Olcs\Transfer\Query\Search\Licence as SearchLicence;
use Common\RefData;

/**
 * Search Result Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ResultController extends AbstractController
{
    public function detailsAction()
    {
        $action = $this->params()->fromRoute('entity') . 'Action';

        if (method_exists($this, $action)) {

            return $this->$action();
        }

        return $this->notFoundAction();
    }

    /**
     * Operator index action
     */
    public function licenceAction()
    {
        $entityId = $this->params()->fromRoute('entityId');

        // retrieve data
        $query = SearchLicence::create(['id' => $entityId]);
        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
        }

        // setup layout and views
        $content = new \Zend\View\Model\ViewModel(
            array_merge(
                [
                    'result' => $result,
                    'soleTraderOrRegisteredCompany' => [
                        RefData::ORG_TYPE_REGISTERED_COMPANY,
                        RefData::ORG_TYPE_SOLE_TRADER,
                    ]
                ],
            $this->generateTables($result)
            )
        );
        $content->setTemplate('olcs/search/search-result');

        $layout = new \Zend\View\Model\ViewModel(
            [
                'pageTitle' => $result['organisation']['name']
            ]
        );
        $layout->setTemplate('layouts/search-result');
        $layout->addChild($content, 'content');

        return $layout;
    }

    private function generateTables($data)
    {
        $tables = [];
        $tableService = $this->getServiceLocator()->get('Table');
        $authService = $this->getServiceLocator()->get('ZfcRbac\Service\AuthorizationService');

        $tables['relatedOperatorLicencesTable'] = $tableService->buildTable(
            '/search-results/related-operator-licences',
            $data['otherLicences']
        );

        $tables['transportManagerTable'] = $tableService->buildTable(
            '/search-results/transport-managers',
            $data['transportManagers']
        );

        if (!($authService->isGranted('partner-admin') || $authService->isGranted('partner-user'))) {
            $tables['operatingCentresTable'] = $tableService->buildTable('/search-results/operating-centres',
                $data['operatingCentres']);
        }

        return $tables;
    }
}

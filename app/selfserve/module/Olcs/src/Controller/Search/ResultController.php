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

/**
 * Search Result Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ResultController extends AbstractController
{

    private $licenceSections = [
        'licence' => 'overview',
        /*'organisation' => 'overview',
        'contactDetails' => 'overview',
        'directors' => 'table',
        'transportManagers' => 'table',
        'operatingCentres' => 'table',
        'vehicles' => 'table',
        'applications' => 'table',
        'conditionUndertakings' => 'table',
        'otherLicences' => 'table'*/
    ];

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

        // setup view

        $content = new \Zend\View\Model\ViewModel(
            array_merge(
                [
                    'result' => $result
                ],
            $this->generateTables($result)
            )
        );

        $content->setTemplate('olcs/search/search-result');

        $layout = new \Zend\View\Model\ViewModel(
            [
                'pageTitle' => 'Big Wagons Limited'
            ]
        );
        $layout->setTemplate('layouts/search-result');
        $layout->addChild($content, 'content');

        return $layout;
    }

    private function generateTables($data)
    {
        $tableService = $this->getServiceLocator()->get('Table');

        return [
            'operatingCentresTable' => $tableService->buildTable('/search-results/operating-centres',
                $data['operatingCentres']),
            'transportManagerTable' => $tableService->buildTable('/search-results/transport-managers',
                $data['transportManagers']),
            'relatedOperatorLicencesTable' => $tableService->buildTable('/search-results/related-operator-licences',
                $data['otherLicences'])
        ];
    }
}

<?php
/**
 * Entity View Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace Olcs\Controller\Entity;

use Common\Controller\Lva\AbstractController;
use Common\Service\Entity\UserEntityService;
use Dvsa\Olcs\Transfer\Query\Search\Licence as SearchLicence;
use Common\RefData;

/**
 * Entity View Controller
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ViewController extends AbstractController
{
    /**
     * Wrapper method to call appropriate entity action
     * @return array
     */
    public function detailsAction()
    {
        $action = $this->params()->fromRoute('entity') . 'Action';

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
        $content->setTemplate('olcs/entity/view');

        $layout = new \Zend\View\Model\ViewModel(
            [
                'pageTitle' => $result['organisation']['name'],
                'pageSubtitle' => $result['organisation']['companyOrLlpNo']
            ]
        );
        $layout->setTemplate('layouts/entity-view');
        $layout->addChild($content, 'content');

        return $layout;
    }

    private function generateTables($data)
    {
        $tables = [];
        $tableService = $this->getServiceLocator()->get('Table');
        $authService = $this->getServiceLocator()->get('ZfcRbac\Service\AuthorizationService');

        $tables['relatedOperatorLicencesTable'] = $tableService->buildTable(
            'entity-view-related-operator-licences',
            $data['otherLicences']
        );

        $tables['transportManagerTable'] = $tableService->buildTable(
            'entity-view-transport-managers',
            $data['transportManagers']
        );

        // this is display logic as partners gets an alternative partner view of operating centres
        if (!($authService->isGranted('partner-admin') || $authService->isGranted('partner-user'))) {
            $tables['operatingCentresTable'] = $tableService->buildTable(
                'entity-view-operating-centres',
                $data['operatingCentres']
            );
        }

        return $tables;
    }
}

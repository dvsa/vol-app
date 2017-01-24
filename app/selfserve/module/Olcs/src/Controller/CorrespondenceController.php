<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\Correspondence\AccessCorrespondence;
use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondence;
use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences;
use Zend\View\Model\ViewModel;

/**
 * Class CorrespondenceController
 *
 * List an operators correspondence.
 *
 * @package Olcs\Controller
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class CorrespondenceController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait,
        Lva\Traits\DashboardNavigationTrait;

    /**
     * Display the table and all the correspondence for the given organisation.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $params = [
            'page' => $this->params()->fromQuery('page', 1),
            'limit' => $this->params()->fromQuery('limit', 10),
            'sort' => $this->params()->fromQuery('sort', 'd.issuedDate'),
            'order' => $this->params()->fromQuery('order', 'DESC'),
            'organisation' => $this->getCurrentOrganisationId(),
            'query' => $this->params()->fromQuery(),
        ];

        $response = $this->handleQuery(Correspondences::create($params));
        if ($response === null || $response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isOk()) {
            $docs = $response->getResult();
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            $docs = [];
        }

        $table = $this->getServiceLocator()->get('Table')
            ->buildTable('correspondence', $this->formatTableData($docs), $params);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('correspondence');

        $count = 0;
        array_walk(
            $docs['results'],
            function ($record) use (&$count) {
                $count += ($record['accessed'] === 'N' ? 1 : 0);
            }
        );

        $this->populateTabCounts($docs['extra']['feeCount'], $count);

        return $view;
    }

    /**
     * A gateway method for accessing a document with method sets the accessed
     * parameter on the correspondence record.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function accessCorrespondenceAction()
    {
        $correspondence = $this->params()->fromRoute('correspondenceId', null);
        $command = AccessCorrespondence::create(
            [
                'id' => $correspondence
            ]
        );

        $response = $this->handleCommand($command);
        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        $query = Correspondence::create(
            [
                'id' => $correspondence
            ]
        );

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        $correspondence = $response->getResult();

        return $this->redirect()->toRoute(
            'getfile',
            array(
                'identifier' => $correspondence['document']['id']
            )
        );
    }

    /**
     * Format the correspondence data for displaying within the table.
     *
     * @param array $docs Corresposdence data
     *
     * @return array
     */
    protected function formatTableData(array $docs = [])
    {
        $docs['results'] = array_map(
            function ($correspondence) {
                return array(
                    'id' => $correspondence['id'],
                    'correspondence' => $correspondence,
                    'licence' => $correspondence['licence'],
                    'date' => $correspondence['createdOn'],
                );
            },
            $docs['results']
        );

        return $docs;
    }
}

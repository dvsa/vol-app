<?php

/**
 * CorrespondenceController.php
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;

use Zend\View\Model\ViewModel;

use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondence;
use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences;
use Dvsa\Olcs\Transfer\Command\Correspondence\AccessCorrespondence;

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
        $query = Correspondences::create(
            [
                'organisation' => $this->getCurrentOrganisationId()
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

        $table = $this->getServiceLocator()->get('Table')
            ->buildTable(
                'correspondence',
                $this->formatTableData($correspondence)
            );

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('correspondence');

        $count = 0;
        array_walk(
            $correspondence['results'],
            function ($record) use (&$count) {
                $count = ($record['accessed'] === 'N' ? $count + 1 : $count);
            }
        );

        $this->populateTabCounts($correspondence['extra']['feeCount'], $count);

        return $view;
    }

    /**
     * A gateway method for accessing a document with method sets the accessed
     * parameter on the correspondence record.
     *
     * @return \Zend\Http\Response
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
     * @param array $correspondences Corresposdence data
     *
     * @return array
     */
    protected function formatTableData(array $correspondences = array())
    {
        return array_map(
            function ($correspondence) {
                return array(
                    'id' => $correspondence['id'],
                    'correspondence' => $correspondence,
                    'licence' => $correspondence['licence'],
                    'date' => $correspondence['createdOn']
                );
            },
            $correspondences['results']
        );
    }
}

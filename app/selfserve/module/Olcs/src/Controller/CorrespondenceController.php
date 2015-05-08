<?php

/**
 * CorrespondenceController.php
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;

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
        $correspondence = $this->getServiceLocator()
            ->get('Entity\CorrespondenceInbox')
            ->getCorrespondenceByOrganisation(
                $this->getCurrentOrganisationId()
            );

        $table = $this->getServiceLocator()->get('Table')
            ->buildTable(
                'correspondence',
                $this->formatTableData($correspondence['Results'])
            );

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('correspondence');

        $count = 0;
        array_walk(
            $correspondence['Results'],
            function ($record) use (&$count) {
                $count = ($record['accessed'] === 'N' ? $count + 1 : $count);
            }
        );

        $this->populateTabCounts($this->getFeeCount(), $count);

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
        if (!is_null($correspondence)) {
            $correspondence = $this->getServiceLocator()
                ->get('Entity\CorrespondenceInbox')
                ->getById($correspondence);
        }

        $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('Lva\AccessCorrespondence')
            ->process($correspondence);

        return $this->redirect()->toRoute(
            'getfile',
            array(
                'file' => $correspondence['document']['identifier'],
                'name' => $correspondence['document']['filename']
            )
        );
    }

    /**
     * Format the correspondence data for displaying within the table.
     *
     * @param array $correspondences
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
            $correspondences
        );
    }
}

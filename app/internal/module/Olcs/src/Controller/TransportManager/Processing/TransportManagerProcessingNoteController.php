<?php

/**
 * Transport Manager Processing Note Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\TransportManager\Processing\AbstractTransportManagerProcessingController;

/**
 * Transport Manager Processing Note Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerProcessingNoteController extends AbstractTransportManagerProcessingController
{
    /**
     * @var string
     */
    protected $section = 'processing-notes';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $tmId = $this->getFromRoute('transportManager');

        $action = $this->getFromPost('action');
        $id = $this->getFromPost('id');

        switch ($action) {
            case 'Add':
                return $this->redirectToRoute(
                    $routePrefix . '/add-note',
                    [
                        'action' => strtolower($action),
                        'noteType' => $noteType,
                        'linkedId' => $linkedId,
                        'licence' => $licenceId,
                        'case' => $caseId,
                        'application' => $applicationId
                    ],
                    [],
                    true
                );
            case 'Edit':
            case 'Delete':
                return $this->redirectToRoute(
                    $routePrefix . '/modify-note',
                    ['action' => strtolower($action), 'id' => $id],
                    [],
                    true
                );
        }

        $table = $this->getNotesTable($tmId, $action);

        $this->loadScripts(['forms/filter', 'table-actions']);

        $view = $this->getViewWithTm(['table' => $table]);
        $view->setTemplate('partials/table');

        return $this->renderView($view);
    }

    /**
     * Gets a list of notes
     *
     * @param int $transportManagerId
     * @param string $action
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function getNotesTable($transportManagerId)
    {
        $routePrefix  = 'transport-manager/processing';
        $noteType     = 'note_t_tm';

        $searchData = array(
            'page' => 1,
            'sort' => 'priority',
            'order' => 'DESC',
            'limit' => 10,
            'noteType' => $noteType,
            'transportManager' => $transportManagerId,
        );

        $requestQuery = $this->getRequest()->getQuery();
        $requestArray = $requestQuery->toArray();

        $filters = array_merge(
            $searchData,
            $requestArray
        );

        //if noteType is set to all
        if (isset($filters['noteType']) && !$filters['noteType']) {
            unset($filters['noteType']);
        }

        $form = $this->getForm('note-filter');
        $form->remove('csrf'); //we never post
        $form->setData($filters);

        $this->setTableFilters($form);

        $bundle = $this->getBundle();

        $resultData = $this->makeRestCall('Note', 'GET', $filters, $bundle);

        $formattedResult = $this->appendRoutePrefix($resultData, $routePrefix);
        // $formattedResult = $resultData;

        $table = $this->getTable(
            'note',
            $formattedResult,
            array_merge(
                $filters,
                array('query' => $requestQuery)
            ),
            true
        );

        return $table;
    }

   /**
     * Gets a bundle for the notes search
     *
     * @return array
     */
    public function getBundle()
    {
        return [
            'children' => [
                'createdBy',
                'noteType',
                'transportManager'
            ]
        ];
    }

    /**
     * Appends the route prefix to each row for the table formatter / url generator
     *
     * @param array $resultData
     * @return array
     */
    public function appendRoutePrefix($resultData, $routePrefix)
    {
        $formatted = [];

        foreach ($resultData['Results'] as $key => $result) {
            $formatted[$key] = $result;
            $formatted[$key]['routePrefix'] = $routePrefix;
        }

        $resultData['Results'] = $formatted;

        return $resultData;
    }
}

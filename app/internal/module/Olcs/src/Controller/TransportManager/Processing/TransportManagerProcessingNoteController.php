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
     * @var string
     */
    protected $routePrefix  = 'transport-manager/processing';

    /**
     * @var string
     */
    protected $noteType = 'note_t_tm';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $tmId = $this->getFromRoute('transportManager');
        $routePrefix = $this->getRoutePrefix();
        $noteType     = $this->getNoteType();

        $action = $this->getFromPost('action');
        $id = $this->getFromPost('id');

        switch ($action) {
            case 'Add':
                return $this->redirectToRoute(
                    $routePrefix . '/add-note',
                    [
                        'action' => strtolower($action),
                        'noteType' => $noteType,
                        'linkedId' => $tmId,
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
     * Adds a note
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $tmId = $this->getFromRoute('transportManager');
        $noteType = $this->getFromRoute('noteType');

        $form = $this->generateFormWithData(
            'licence-notes', // @TODO change form name
            'processAddNotes',
            array(
                'transportManager' => $tmId,
                'noteType' => $noteType,
            )
        );

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }


        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'internal.transport-manager.processing.notes.add.title');
    }

    /**
     * Processes the add note form
     *
     * @param array $data
     * @return \Zend\Http\Response
     * @throws \Common\Exception\BadRequestException
     */
    public function processAddNotes($data)
    {
        $user = $this->getLoggedInUser();

        $data = array_merge($data, $data['main']);
        $data['createdBy'] = $user;
        $data['lastModifiedBy'] = $user;

        $result = $this->processAdd($data, 'Note');

        if (isset($result['id'])) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute($this->getRoutePrefix() . '/add-note', ['action' => 'Add'], [], true);
    }


    /**
     * Edits a note
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $id = $this->getFromRoute('id');
        $note = $this->makeRestCall('Note', 'GET', ['id' => $id], $this->getBundle());

        $data = [
            'main' => [
                'comment' => $note['comment'],
                'priority' => $note['priority']
            ],
            'id' => $note['id'],
            'version' => $note['version']
        ];

        $form = $this->generateFormWithData('licence-edit-notes', 'processEditNotes', $data);

        if ($this->getResponse()->getContent() !== '') {
            return $this->getResponse();
        }

        $form->get('main')
            ->get('comment')
            ->setAttribute('disabled', 'disabled');

        $view = $this->getView(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'internal.transport-manager.processing.notes.modify.title');
    }

    /**
     * Processes the edit note form
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processEditNotes($data)
    {
        $data = array_merge($data, $data['main']);

        //don't allow note type, linkedId or comment to be changed
        unset($data['noteType'], $data['linkedId'], $data['transportManager'], $data['comment']);

        $data['lastModifiedBy'] = $this->getLoggedInUser();

        $result = $this->processEdit($data, 'Note');

        if (empty($result)) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute($this->getRoutePrefix() . '/modify-note', ['action' => 'Edit'], [], true);
    }

    /**
     * Gets a list of notes
     *
     * @param int $transportManagerId
     * @param string $action
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    protected  function getNotesTable($transportManagerId)
    {
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

        $formattedResult = $this->appendRoutePrefix($resultData, $this->getRoutePrefix());
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
    protected function getBundle()
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
    protected function appendRoutePrefix($resultData, $routePrefix)
    {
        $formatted = [];

        foreach ($resultData['Results'] as $key => $result) {
            $formatted[$key] = $result;
            $formatted[$key]['routePrefix'] = $routePrefix;
        }

        $resultData['Results'] = $formatted;

        return $resultData;
    }

    protected function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    protected function getNoteType()
    {
        return $this->noteType;
    }
}

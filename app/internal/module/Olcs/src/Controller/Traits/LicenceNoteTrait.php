<?php

/**
 * Licence Note Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Traits;

use Common\Exception\BadRequestException;

/**
 * Licence Note Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait LicenceNoteTrait
{
    private $templatePrefix;
    private $routePrefix;

    /**
     * Allows the template to change based on the controller being used
     *
     * @return string
     */
    public function getTemplatePrefix()
    {
        return $this->templatePrefix;
    }

    /**
     * Allows the template to change based on the controller being used
     *
     * @param string $templatePrefix
     */
    public function setTemplatePrefix($templatePrefix)
    {
        $this->templatePrefix = $templatePrefix;
    }

    /**
     * Allows the route to change based on the controller being used
     *
     * @return string
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    /**
     * Allows the route to change based on the controller being used
     *
     * @param string $routePrefix
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routePrefix = $routePrefix;
    }

    /**
     * Redirects to the index page, dependant on the note type
     *
     * @return \Zend\Http\Response
     */
    public function redirectToIndex()
    {
        $this->redirectToRoute($this->getRoutePrefix() . '/notes', [], [], true);
    }

    public function getNotesList($licenceId, $linkedId, $noteType = 'note_t_lic', $action = null, $id = null)
    {
        $routePrefix  = $this->getRoutePrefix();

        switch ($action) {
            case 'Add':
                return $this->redirectToRoute(
                    $routePrefix . '/add-note',
                    [
                        'action' => strtolower($action),
                        'licence' => $licenceId,
                        'noteType' => $noteType,
                        'linkedId' => $linkedId
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

        $searchData = [];

        if ($noteType != 'note_t_lic') {
            $searchData['noteType'] = $noteType;
        }

        $searchData['licence'] = $licenceId;
        $searchData['page'] = $this->getFromRoute('page', 1);
        $searchData['sort'] = $this->getFromRoute('sort', 'priority');
        $searchData['order'] = $this->getFromRoute('order', 'desc');
        $searchData['limit'] = $this->getFromRoute('limit', 10);
        $searchData['url'] = $this->url();

        $bundle = $this->getBundle();

        $resultData = $this->makeRestCall('Note', 'GET', $searchData, $bundle);

        $formattedResult = $this->appendLinkedId($resultData);

        $table = $this->buildTable('note', $formattedResult, $searchData);

        $view = $this->getView(['table' => $table]);
        $view->setTemplate($this->getTemplatePrefix() . '/notes/index');

        return $view;
    }

    /**
     * Adds a note
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $licenceId = $this->getFromRoute('licence');
        $noteType = $this->getFromRoute('noteType');
        $linkedId = $this->getFromRoute('linkedId');

        $form = $this->generateFormWithData(
            'licence-notes',
            'processAddNotes',
            array(
                'licence' => $licenceId,
                'noteType' => $noteType,
                'linkedId' => $linkedId
            )
        );

        $view = $this->getView(['form' => $form]);
        $view->setTemplate($this->getTemplatePrefix() . '/notes/form');

        return $this->renderView($view);
    }

    public function processAddNotes($data)
    {
        $user = $this->getLoggedInUser();

        $data = array_merge($data, $data['main']);
        $data['createdBy'] = $user;
        $data['lastModifiedBy'] = $user;

        //checks which field to add in the linked id to
        $field = $this->getIdField($data['noteType']);

        //if this is a licence note this isn't needed, for other types of note it is expected
        if ($field) {
            if (!(int)$data['linkedId']) {
                throw new BadRequestException('Unable to link your note to the correct record');
            }

            $data[$field] = $data['linkedId'];
        }

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

        $bundle = $this->getBundle();
        $note = $this->makeRestCall('Note', 'GET', array('id' => $id), $bundle);

        $data = [
            'main' => [
                'comment' => $note['comment'],
                'priority' => $note['priority']
            ],
            'id' => $note['id'],
            'version' => $note['version']
        ];

        $form = $this->generateFormWithData(
            'licence-edit-notes',
            'processEditNotes',
            $data
        );

        $form->get('main')
            ->get('comment')
            ->setAttribute('disabled', 'disabled');

        $view = $this->getView(['form' => $form]);
        $view->setTemplate($this->getTemplatePrefix() . '/notes/form');

        return $this->renderView($view);
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

        //don't allow licence, note type or linkedId or comment to be changed
        unset($data['licence'], $data['noteType'], $data['linkedId'], $data['comment']);

        $data['lastModifiedBy'] = $this->getLoggedInUser();

        $result = $this->processEdit($data, 'Note');

        if (empty($result)) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute($this->getRoutePrefix() . '/modify-note', ['action' => 'Edit'], [], true);
    }

    public function appendLinkedId($resultData)
    {
        $formatted = [];

        foreach ($resultData['Results'] as $key => $result) {
            $field = $this->getIdField($result['noteType']['id']);

            $formatted[$key] = $result;
            $formatted[$key]['noteType']['description'] =
                $result['noteType']['description'] . ' ' . (isset($result[$field]['id']) ? $result[$field]['id'] : '');
        }

        $resultData['Results'] = $formatted;

        return $resultData;
    }

    /**
     * Returns the field name used for linking the id to the appropriate record type
     *
     * @param $noteType
     * @return string
     */
    public function getIdField($noteType)
    {
        $field = '';

        switch ($noteType) {
            case 'note_t_lic':
                $field = 'licence';
                break;
            case 'note_t_app':
                $field = 'application';
                break;
            case 'note_t_irfo_gv':
                $field = 'irfoGvPermit';
                break;
            case 'note_t_irfo_psv':
                $field = 'irfoPsvAuth';
                break;
            case 'note_t_case':
                $field = 'case';
                break;
            case 'note_t_bus':
                $field = 'busReg';
                break;
        }

        return $field;
    }

    /**
     * Returns the name of the service used to perform a delete
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        return 'Note';
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
                'createdBy' => [
                    'properties' => [
                        'name'
                    ]
                ],
                'noteType' => [
                    'properties' => [
                        'id',
                        'description'
                    ]
                ],
                'licence' => [
                    'properties' => [
                        'id'
                    ]
                ],
                'application' => [
                    'properties' => [
                        'id'
                    ]
                ],
                'irfoGvPermit' => [
                    'properties' => [
                        'id'
                    ]
                ],
                'irfoPsvAuth' => [
                    'properties' => [
                        'id'
                    ]
                ],
                'case' => [
                    'properties' => [
                        'id'
                    ]
                ],
                'busReg' => [
                    'properties' => [
                        'id'
                    ]
                ]
            ]
        ];
    }
}

<?php

/**
 * Operator Processing Note Controller
 */
namespace Olcs\Controller\Operator;

use Olcs\Controller\Traits\NotesActionTrait;

/**
 * Operator Processing Note Controller
 */
class OperatorProcessingNoteController extends OperatorController
{
    use NotesActionTrait;

    /**
     * @var string
     */
    protected $section = 'processing_note';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_processing';

    /**
     * @var string
     */
    protected $service = 'Note';

    /**
     * @var string needed for NotesActionTrait magic
     */
    protected $entity = 'organisation';

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity;
    }

    /**
     * Constructor - sets template and route prefix for use in LicenceNote trait
     */
    public function __construct()
    {
        $this->setRoutePrefix('operator/processing/notes');
    }

    /**
     * Brings back a list of notes based on the search
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $organisationId = $this->getFromRoute('organisation');
        $action = $this->getFromPost('action');
        $id = $this->getFromPost('id');

        $notesResult = $this->getNotesList(null, $organisationId, 'note_t_org', $action, $id);

        //if a ViewModel has been returned
        if ($notesResult instanceof \Zend\View\Model\ViewModel) {
            return $this->renderView($notesResult);
        }

        //if a redirect has been returned
        return $notesResult;
    }

    /**
     * Defines the controller specific notes table params
     *
     * @return array
     */
    protected function getNotesTableParams()
    {
        return [
            'organisation' => $this->getFromRoute('organisation')
        ];
    }

    /**
     * Sets the table filters.
     *
     * @param mixed $filters
     */
    public function setTableFilters($filters)
    {
        // does nothing as we don't want to have filters on this page
    }
}

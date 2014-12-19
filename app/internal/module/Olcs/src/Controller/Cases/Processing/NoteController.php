<?php

/**
 * Case note controller
 * Case note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Processing;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case note controller
 * Case note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class NoteController extends OlcsController\CrudAbstract
{
    use ControllerTraits\LicenceNoteTrait;
    use ControllerTraits\CaseControllerTrait;

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    /**
     * @var string needed for LicenceNoteTrait magic
     */
    protected $entity = 'case';

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity;
    }

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_notes';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Note';

    public function __construct()
    {
        $this->setTemplatePrefix('case/processing');
        $this->setRoutePrefix('case_processing_notes');
        $this->setRedirectIndexRoute('');
    }

    /**
     * Brings back a list of notes based on the search
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $licenceId = $this->getFromRoute('licence');
        $caseId = $this->getFromRoute('case');

        //unable to use checkForCrudAction() as add and edit/delete require different routes
        $action = $this->getFromPost('action');
        $id = $this->getFromPost('id');

        $notesResult = $this->getNotesList($licenceId, $caseId, 'note_t_case', $action, $id, $caseId);

        //if a ViewModel has been returned
        if ($notesResult instanceof \Zend\View\Model\ViewModel) {
            return $this->renderView($notesResult);
        }

        //if a redirect has been returned
        return $notesResult;
    }
}

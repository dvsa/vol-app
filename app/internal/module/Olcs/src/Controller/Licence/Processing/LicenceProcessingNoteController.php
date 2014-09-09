<?php

/**
 * Note controller
 * Licence note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Licence\Processing;

use Common\Controller\CrudInterface;
use Olcs\Controller\Traits\DeleteActionTrait;
use Olcs\Controller\Traits\LicenceNoteTrait;

/**
 * Note controller
 * Licence note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingNoteController extends AbstractLicenceProcessingController implements CrudInterface
{
    use DeleteActionTrait;
    use LicenceNoteTrait;

    protected $section = 'notes';

    public function __construct()
    {
        $this->setTemplatePrefix('licence/processing');
        $this->setRoutePrefix('licence/processing');
    }

    /**
     * Brings back a list of notes based on the search
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $licenceId = $this->getFromRoute('licence');

        //unable to use checkForCrudAction() as add and edit/delete require different routes
        $action = $this->getFromPost('action');
        $id = $this->getFromPost('id');

        $notesResult = $this->getNotesList($licenceId, $licenceId, 'note_t_lic', $action, $id);

        //if a ViewModel has been returned
        if ($notesResult instanceof \Zend\View\Model\ViewModel) {
            //$notesResult->setTemplate('licence/processing/notes/index');
            return $this->renderView($notesResult);
        }

        //if a redirect has been returned
        return $notesResult;
    }
}

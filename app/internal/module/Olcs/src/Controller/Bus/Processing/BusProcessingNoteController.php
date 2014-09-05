<?php

/**
 * Bus Processing Note controller
 * Bus note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Common\Controller\CrudInterface;
use Olcs\Controller\Traits\DeleteActionTrait;
use Olcs\Controller\Traits\LicenceNoteTrait;

/**
 * Bus Processing Note controller
 * Bus note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingNoteController extends BusProcessingController implements CrudInterface
{
    use DeleteActionTrait;
    use LicenceNoteTrait;

    protected $section = 'notes';

    public function __construct()
    {
        $this->setTemplatePrefix('licence/bus/processing');
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

        //$view->setTemplate('licence/bus/index');


        $view = $this->getNotesList($licenceId, 'note_t_bus', $action, $id);
        return $this->viewVars($view, 'licence_bus_processing');
    }
}

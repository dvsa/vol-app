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
    use LicenceNoteTrait;

    protected $identifierName = 'id';
    protected $item = 'notes';
    protected $service = 'Note';

    /**
     * Constructor - sets template and route prefix for use in LicenceNote trait
     */
    public function __construct()
    {
        $this->setTemplatePrefix('licence/bus/processing');
        $this->setRoutePrefix('licence/bus-processing');
        $this->setRedirectIndexRoute('/notes');
    }

    /**
     * Brings back a list of notes based on the search
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $licenceId = $this->getFromRoute('licence');
        $busReg = $this->getFromRoute('busRegId');
        $action = $this->getFromPost('action');
        $id = $this->getFromPost('id');

        $notesResult = $this->getNotesList($licenceId, $busReg, 'note_t_bus', $action, $id);

        //if a ViewModel has been returned
        if ($notesResult instanceof \Zend\View\Model\ViewModel) {
            return $this->renderView($notesResult);
        }

        //if a redirect has been returned
        return $notesResult;
    }
}

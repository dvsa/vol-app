<?php

/**
 * Note controller
 * Search for operators and licences
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\CrudInterface;
use Common\Controller\FormActionController;
use Olcs\Controller\Traits\DeleteActionTrait;
use Zend\View\Model\ViewModel;

/**
 * Note controller
 * Search for operators and licences
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class NoteController extends FormActionController implements CrudInterface
{
    use DeleteActionTrait;

    public function indexAction()
    {

    }

    public function addAction()
    {

    }

    public function editAction()
    {

    }

    public function processAddAction()
    {

    }

    public function processEditAction()
    {

    }

    public function getDeleteServiceName()
    {
        return 'Note';
    }


}
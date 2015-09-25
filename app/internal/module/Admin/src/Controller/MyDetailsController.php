<?php

/**
 * My Details Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccount as UpdateDto;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\MyDetails as Mapper;
use Admin\Form\Model\Form\MyDetails as Form;
use Zend\View\Model\ViewModel;

/**
 * My Details Controller
 */
class MyDetailsController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-my-account';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-my-account',
                'navigationTitle' => 'My account'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;
    protected $itemParams = [];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;

    protected $editContentTitle = 'My details';

    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    public function addAction()
    {
        return $this->notFoundAction();
    }

    public function editAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'My account');

        return parent::editAction();
    }

    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'admin-dashboard/admin-my-account/details',
            ['action' => 'edit'],
            ['code' => '303'],
            true
        );
    }
}

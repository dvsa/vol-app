<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\MyDetails as Form;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccount as UpdateDto;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as ItemDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\MyDetails as Mapper;

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
    protected $navigationId = 'admin-dashboard/admin-your-account';

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

    protected $editContentTitle = 'Your account';

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-your-account',
                'navigationTitle' => 'Your account'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    /**
     * Edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Your account');

        return parent::editAction();
    }

    /**
     * Redirect to index
     *
     * @return \Laminas\Http\Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'admin-dashboard/admin-your-account/details',
            ['action' => 'edit'],
            ['code' => '303'],
            true
        );
    }
}

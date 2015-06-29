<?php

/**
 * My Details Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccount as UpdateDto;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\MyDetails as Mapper;
use Admin\Form\Model\Form\MyDetails as Form;

/**
 * My Details Controller
 */
class MyDetailsController extends AbstractInternalController implements
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-my-account';

    public function getPageLayout()
    {
        return 'layout/admin-my-details';
    }

    public function getPageInnerLayout()
    {
        return 'layout/wide-layout';
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

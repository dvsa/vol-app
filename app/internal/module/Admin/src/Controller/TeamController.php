<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Common\Controller\Traits\GenericRenderView;
use Common\Service\Data\Search\Search;
use Dvsa\Olcs\Transfer\Command\User\CreateUser as CreateDto;
use Dvsa\Olcs\Transfer\Command\User\UpdateUser as UpdateDto;
use Dvsa\Olcs\Transfer\Command\User\DeleteUser as DeleteDto;
use Dvsa\Olcs\Transfer\Query\User\User as ItemDto;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList as TransportManagerApplicationListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\User as Mapper;
use Admin\Form\Model\Form\User as Form;
use Zend\View\Model\ViewModel;

/**
 * User Management Controller
 *
 * @method redirect Zend\Mvc\Controller\Plugin\Redirect
 */
class TeamController extends AbstractInternalController implements LeftViewProvider
{
    use GenericRenderView;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-team-management';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-user-management',
                'navigationTitle' => 'User management'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');

        $this->setNavigationId('admin-dashboard/admin-team-management');

        return $this->renderView($view, 'Admin');
    }

    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }
}

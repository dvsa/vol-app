<?php

namespace Admin\Controller;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Common\Controller\Traits\GenericRenderView;
use Zend\View\Model\ViewModel;

/**
 * Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @method \Common\Service\Cqrs\Response handleQuery(\Dvsa\Olcs\Transfer\Query\QueryInterface $query)
 * @method \Common\Service\Cqrs\Response handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $query)
 * @method \Common\Controller\Plugin\Redirect redirect()
 * @method \Zend\Http\Response handleCrudAction($crudAction)
 */
abstract class AbstractController extends ZendAbstractActionController implements LeftViewProvider
{
    use GenericRenderView;

    /**
     * Get Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/sections/admin/partials/left');

        return $view;
    }

    /**
     * Set Navigation Id
     *
     * @param string $id Nav Id
     *
     * @return void
     */
    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }
}

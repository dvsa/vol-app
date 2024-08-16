<?php

namespace Admin\Controller;

use Common\Controller\Traits\GenericRenderView;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @method \Common\Service\Cqrs\Response handleQuery(\Dvsa\Olcs\Transfer\Query\QueryInterface $query)
 * @method \Common\Service\Cqrs\Response handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $query)
 * @method \Common\Controller\Plugin\Redirect redirect()
 */
abstract class AbstractController extends LaminasAbstractActionController implements LeftViewProvider
{
    use GenericRenderView;

    public function __construct(protected Placeholder $placeholder)
    {
    }

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
        $this->placeholder->getContainer('navigationId')->set($id);
    }
}

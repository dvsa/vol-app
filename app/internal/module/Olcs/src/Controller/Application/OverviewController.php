<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\View\Model\ViewModel;
use Olcs\View\Model\Application\Layout;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractApplicationController
{
    /**
     * Application overview
     */
    public function indexAction()
    {
        $content = new ViewModel();
        $content->setTemplate('application/overview');

        return $this->getLayout($content);
    }

    /**
     * Get the layout view model
     *
     * @return \Olcs\View\Model\Lva\Application\Layout
     */
    protected function getLayout($content)
    {
        $params = array();

        return new Layout(
            $content,
            $this->getQuickActions(),
            $params
        );
    }

    /**
     * Quick action view model
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function getQuickActions()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('application/quick-actions');
        return $viewModel;
    }
}

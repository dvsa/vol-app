<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\View\Model\ViewModel;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Application\ApplicationLayout;
use Olcs\View\Model\Application\SectionLayout;
use Olcs\View\Model\Application\MainNav;

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

        return $this->render($content);
    }

    /**
     * Render the section
     *
     * @param ViewModel $content
     */
    protected function render(ViewModel $content)
    {
        $sectionLayout = new SectionLayout();
        $sectionLayout->addChild($content, 'content');

        $applicationLayout = new ApplicationLayout();

        $applicationLayout->addChild(new MainNav(), 'nav');
        $applicationLayout->addChild($this->getQuickActions(), 'actions');
        $applicationLayout->addChild($sectionLayout, 'content');

        $params = $this->getHeaderParams();

        return new Layout($applicationLayout, $params);
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

    /**
     * Get headers params
     *
     * @return array
     */
    protected function getHeaderParams()
    {
        $data = $this->getEntityService('Application')->getHeaderData($this->getApplicationId());

        return array(
            'applicationId' => $data['id'],
            'licNo' => $data['licence']['licNo'],
            'licenceId' => $data['licence']['id'],
            'companyName' => $data['licence']['organisation']['name'],
            'status' => $data['status']['id']
        );
    }
}

<?php

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\View\Model\ViewModel;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Application\SectionLayout;
use Olcs\View\Model\Application\ApplicationLayout;
use Olcs\Controller\AbstractInternalController;
use Common\Service\Entity\ApplicationCompletionService;
use Common\Controller\Traits\Lva\ApplicationControllerTrait;

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractApplicationController extends AbstractInternalController
{
    use ApplicationControllerTrait;

    /**
     * Render the section
     *
     * @param ViewModel $content
     */
    protected function render(ViewModel $content)
    {
        $sectionLayout = new SectionLayout(array('sections' => $this->getSectionsForView()));
        $sectionLayout->addChild($content, 'content');

        $applicationLayout = new ApplicationLayout();

        $applicationLayout->addChild($this->getQuickActions(), 'actions');
        $applicationLayout->addChild($sectionLayout, 'content');

        $params = $this->getHeaderParams();

        return new Layout($applicationLayout, $params);
    }

    /**
     * Get the sections for the view
     *
     * @return array
     */
    protected function getSectionsForView()
    {
        $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        $applicationStatuses = $this->getCompletionStatuses($this->getApplicationId());
        $filter = $this->getHelperService('StringHelper');

        $sections = array(
            'overview' => array('class' => 'no-background', 'current' => ($routeName === 'lva-application'))
        );

        foreach ($this->getAccessibleSections() as $section) {

            $statusIndex = lcfirst($filter->underscoreToCamel($section)) . 'Status';

            $class = '';
            switch ($applicationStatuses[$statusIndex]) {
                case ApplicationCompletionService::STATUS_COMPLETE:
                    $class = 'complete';
                    break;
                case ApplicationCompletionService::STATUS_INCOMPLETE:
                    $class = 'incomplete';
                    break;
            }

            $sections[$section] = array('class' => $class, 'current' => ('lva-application/' . $section === $routeName));
        }

        return $sections;
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

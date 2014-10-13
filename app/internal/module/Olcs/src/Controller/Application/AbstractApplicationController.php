<?php

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\View\Model\Section;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Application\SectionLayout;
use Olcs\View\Model\Application\ApplicationLayout;
use Olcs\Controller\AbstractInternalController;
use Common\Service\Entity\ApplicationCompletionService;
use Common\Controller\Traits\Lva\ApplicationControllerTrait;
use Common\Controller\Traits\Lva\EnabledSectionTrait;

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractApplicationController extends AbstractInternalController
{
    use ApplicationControllerTrait,
        EnabledSectionTrait;

    /**
     * Holds the lva type
     *
     * @var string
     */
    protected $lva = 'application';

    /**
     * Render the section
     *
     * @param string|ViewModel $content
     * @param \Zend\Form\Form $form
     * @return \Zend\View\Model\ViewModel
     */
    protected function render($content, Form $form = null)
    {
        if (! ($content instanceof ViewModel)) {
            $content = new Section(array('title' => 'lva.section.title.' . $content, 'form' => $form));
        }

        $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $sectionLayout = new SectionLayout(
            array('sections' => $this->getSectionsForView(), 'currentRoute' => $routeName)
        );
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
        $applicationStatuses = $this->getCompletionStatuses($this->getApplicationId());
        $filter = $this->getHelperService('StringHelper');

        $sections = array(
            'overview' => array('class' => 'no-background', 'route' => 'lva-' . $this->lva, 'enabled' => true)
        );

        $accessibleSections = $this->setEnabledFlagOnSections(
            $this->getAccessibleSections(false),
            $applicationStatuses
        );

        foreach ($accessibleSections as $section => $settings) {

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

            $sections[$section] = array_merge(
                $settings,
                array('class' => $class, 'route' => 'lva-' . $this->lva . '/' . $section)
            );
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

<?php

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Olcs\View\Model\Application\SectionLayout;
use Common\View\Model\Section;
use Common\Controller\Lva\Traits\CommonApplicationControllerTrait;
use Common\Service\Entity\ApplicationCompletionEntityService;
use Olcs\Controller\Traits\ApplicationControllerTrait as GenericInternalApplicationControllerTrait;

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    use InternalControllerTrait,
        CommonApplicationControllerTrait,
        GenericInternalApplicationControllerTrait {
            GenericInternalApplicationControllerTrait::render as genericRender;
        }

    /**
     * Render the section
     *
     * @param string|ViewModel $content
     * @param \Zend\Form\Form $form
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function render($content, Form $form = null, $variables = array())
    {
        if (! ($content instanceof ViewModel)) {
            $content = new Section(array('title' => 'lva.section.title.' . $content, 'form' => $form));
        }

        $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $params = array_merge(
            array(
                'sections'     => $this->getSectionsForView(),
                'currentRoute' => $routeName,
                'lvaId'        => $this->getIdentifier()
            ),
            $variables
        );
        $sectionLayout = new SectionLayout($params);
        $sectionLayout->addChild($content, 'content');

        return $this->genericRender($sectionLayout);
    }

    /**
     * Get the sections for the view
     *
     * @return array
     */
    protected function getSectionsForView()
    {
        $applicationStatuses = $this->getCompletionStatuses($this->getApplicationId());
        $filter = $this->getServiceLocator()->get('Helper\String');

        $sections = array(
            'overview' => array('class' => 'no-background', 'route' => 'lva-' . $this->lva, 'enabled' => true)
        );

        $accessibleSections = $this->setEnabledAndCompleteFlagOnSections(
            $this->getAccessibleSections(false),
            $applicationStatuses
        );

        foreach ($accessibleSections as $section => $settings) {

            $statusIndex = lcfirst($filter->underscoreToCamel($section)) . 'Status';

            $class = '';
            switch ($applicationStatuses[$statusIndex]) {
                case ApplicationCompletionEntityService::STATUS_COMPLETE:
                    $class = 'complete';
                    break;
                case ApplicationCompletionEntityService::STATUS_INCOMPLETE:
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
}

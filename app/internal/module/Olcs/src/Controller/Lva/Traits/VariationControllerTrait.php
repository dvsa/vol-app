<?php

/**
 * INTERNAL Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\View\Model\Section;
use Olcs\View\Model\Variation\VariationLayout;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Variation\SectionLayout;
use Common\Controller\Lva\Traits\CommonVariationControllerTrait;
use Common\Service\Entity\VariationCompletionEntityService;

/**
 * INTERNAL Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait VariationControllerTrait
{
    use ApplicationControllerTrait,
        CommonVariationControllerTrait {
            CommonVariationControllerTrait::preDispatch insteadof ApplicationControllerTrait;
            CommonVariationControllerTrait::postSave insteadof ApplicationControllerTrait;
            CommonVariationControllerTrait::goToNextSection insteadof ApplicationControllerTrait;
        }

    /**
     * Render the section
     *
     * @param string|ViewModel $content
     * @param \Zend\Form\Form $form
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function render($content, Form $form = null, $variables = [])
    {
        if (!($content instanceof ViewModel)) {
            $sectionParams = array_merge(
                array('title' => 'lva.section.title.' . $content, 'form' => $form),
                $variables
            );

            $content = new Section($sectionParams);
        }

        $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $sectionLayout = new SectionLayout(
            array_merge(
                $variables,
                array(
                    'sections'     => $this->getSectionsForView(),
                    'currentRoute' => $routeName,
                    'lvaId'        => $this->getIdentifier()
                )
            )
        );
        $sectionLayout->addChild($content, 'content');

        $applicationLayout = new VariationLayout();

        $applicationLayout->addChild($this->getQuickActions(), 'actions');
        $applicationLayout->addChild($sectionLayout, 'content');

        $params = $this->getHeaderParams();

        return new Layout($applicationLayout, $params);
    }

    /**
     * Get application status
     *
     * @params int $applicationId
     * @return array
     */
    protected function getCompletionStatuses($applicationId)
    {
        return $this->getServiceLocator()->get('Entity\VariationCompletion')->getCompletionStatuses($applicationId);
    }

    /**
     * Get the sections for the view
     *
     * @return array
     */
    protected function getSectionsForView()
    {
        $variationStatuses = $this->getCompletionStatuses($this->getApplicationId());

        $sections = array(
            'overview' => array('class' => 'no-background', 'route' => 'lva-variation')
        );

        $accessibleSections = $this->getAccessibleSections(false);

        foreach ($accessibleSections as $section => $settings) {

            $class = '';
            switch ($variationStatuses[$section]) {
                case VariationCompletionEntityService::STATUS_UPDATED:
                    $class = 'edited';
                    break;
                case VariationCompletionEntityService::STATUS_REQUIRES_ATTENTION:
                    $class = 'incomplete';
                    break;
            }

            $sections[$section] = array_merge(
                $settings,
                array('class' => $class, 'route' => 'lva-variation/' . $section)
            );
        }

        return $sections;
    }
}

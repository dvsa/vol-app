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
                array('sections' => $this->getSectionsForView(), 'currentRoute' => $routeName)
            )
        );
        $sectionLayout->addChild($content, 'content');

        $applicationLayout = new VariationLayout();

        $applicationLayout->addChild($this->getQuickActions(), 'actions');
        $applicationLayout->addChild($sectionLayout, 'content');

        $params = $this->getHeaderParams();

        return new Layout($applicationLayout, $params);
    }
}

<?php

/**
 * INTERNAL Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Olcs\View\Model\Variation\VariationLayout;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Variation\SectionLayout;
use Olcs\Controller\Application\AbstractApplicationController;

/**
 * INTERNAL Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractVariationController extends AbstractApplicationController
{
    /**
     * Lva
     *
     * @var string
     */
    protected $lva = 'variation';

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

        $applicationLayout = new VariationLayout();

        $applicationLayout->addChild($this->getQuickActions(), 'actions');
        $applicationLayout->addChild($sectionLayout, 'content');

        $params = $this->getHeaderParams();

        return new Layout($applicationLayout, $params);
    }
}

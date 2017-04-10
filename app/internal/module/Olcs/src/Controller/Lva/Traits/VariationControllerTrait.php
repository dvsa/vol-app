<?php

/**
 * INTERNAL Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\View\Model\Section;
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
     * render page
     * renderPage
     *
     * @param ViewModel $content   content
     * @param string    $title     title
     * @param array     $variables variables
     *
     * @return ViewModel
     */
    protected function renderPage($content, $title = '', array $variables = [])
    {
        if ($title) {
            $this->placeholder()->setPlaceholder('contentTitle', $title);
        }

        $layout = $this->viewBuilder()->buildView($content);

        if (!($this instanceof LeftViewProvider)) {
            $left = $this->getLeft($variables);

            if ($left !== null) {
                $layout->setLeft($this->getLeft($variables));
            }
        }

        return $layout;
    }

    /**
     * get method left
     *
     * @param array $variables variables
     *
     * @return ViewModel
     */
    protected function getLeft(array $variables = [])
    {
        $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $left = new ViewModel(
            array_merge(
                [
                    'sections'     => $this->getSectionsForView(),
                    'currentRoute' => $routeName,
                    'lvaId'        => $this->getIdentifier()
                ],
                $variables
            )
        );
        $left->setTemplate('sections/variation/partials/left');

        return $left;
    }

    /**
     * get Method right
     *
     * @return ViewModel
     */
    protected function getRight()
    {
        $right = new ViewModel();
        $right->setTemplate('sections/variation/partials/right');

        return $right;
    }

    /**
     * Render the section
     *
     * @param string|ViewModel $content   content
     * @param \Zend\Form\Form  $form      form
     * @param array            $variables variables
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function render($content, Form $form = null, $variables = [])
    {
        if (!($content instanceof ViewModel)) {
            $sectionParams = array_merge(
                [
                    'form' => $form
                ],
                $variables
            );

            if ($content === 'people') {
                $title = $form->get('table')->get('table')->getTable()->getVariable('title');
            } else {
                $title = 'lva.section.title.' . $content;
            }

            $content = new Section($sectionParams);

            return $this->renderPage($content, $title, $variables);
        }

        return $this->renderPage($content, $content->getVariable('title'), $variables);
    }

    /**
     * Get the sections for the view
     *
     * @return array
     */
    protected function getSectionsForView()
    {
        $applicationData = $this->getApplicationData($this->getApplicationId());
        $variationStatuses = $applicationData['applicationCompletion'];
        $filter = $this->getServiceLocator()->get('Helper\String');

        $sections = array(
            'overview' => array('class' => 'no-background', 'route' => 'lva-variation')
        );

        $status = $applicationData['status']['id'];
        // if status is valid then only show Overview section
        if ($status === \Common\RefData::APPLICATION_STATUS_VALID) {
            return $sections;
        }

        $accessibleSections = $this->getAccessibleSections(false);

        foreach ($accessibleSections as $section => $settings) {

            $statusIndex = lcfirst($filter->underscoreToCamel($section)) . 'Status';

            $class = '';
            switch ($variationStatuses[$statusIndex]) {
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

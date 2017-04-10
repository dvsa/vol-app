<?php

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
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
     * Hook into the dispatch before the controller action is executed
     *
     * @return array|bool
     */
    protected function preDispatch()
    {
        if ($this->isApplicationVariation()) {
            $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
            $newRouteName = str_replace('lva-application', 'lva-variation', $routeName);

            return $this->redirect()->toRoute($newRouteName, [], [], true);
        }

        return $this->checkForRedirect($this->getApplicationId());
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
    protected function render($content, Form $form = null, $variables = array())
    {
        if (! ($content instanceof ViewModel)) {
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

            $content = new ViewModel($sectionParams);
            $content->setTemplate('sections/lva/lva-details');

            return $this->genericRender($content, $title, $variables);
        }

        return $this->genericRender($content, $content->getVariable('title'), $variables);
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
        $left->setTemplate('sections/application/partials/left');

        return $left;
    }

    /**
     * Get the sections for the view
     *
     * @return array
     */
    protected function getSectionsForView()
    {
        $applicationCompletion = $this->getApplicationData($this->getApplicationId());
        $applicationStatuses = $applicationCompletion['applicationCompletion'];
        $filter = $this->getServiceLocator()->get('Helper\String');

        $sections = array(
            'overview' => array('class' => 'no-background', 'route' => 'lva-application', 'enabled' => true)
        );

        $status = $applicationCompletion['status']['id'];
        // if status is valid then only show Overview section
        if ($status === \Common\RefData::APPLICATION_STATUS_VALID) {
            return $sections;
        }

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
                array('class' => $class, 'route' => 'lva-application/' . $section)
            );
        }

        return $sections;
    }

    /**
     * Get application data
     *
     * @param int $applicationId applicationId
     *
     * @return null|array
     * @throws \RuntimeException
     */
    protected function getApplicationData($applicationId)
    {
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Application\Application::create(['id' => $applicationId])
        );

        if ($response->isNotFound()) {
            return null;
        }
        if (!$response->isOk()) {
            throw new \RuntimeException('Failed to get Application data');
        }

        return $response->getResult();
    }
}

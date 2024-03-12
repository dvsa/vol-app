<?php

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Lva\Traits\CommonApplicationControllerTrait;
use Common\RefData;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Traits\ApplicationControllerTrait as GenericInternalApplicationControllerTrait;

/**
 * INTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    use InternalControllerTrait, CommonApplicationControllerTrait, GenericInternalApplicationControllerTrait {
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
     * @param string|ViewModel   $content   content
     * @param \Laminas\Form\Form $form      form
     * @param array              $variables variables
     *
     * @return \Laminas\View\Model\ViewModel|null
     */
    protected function render($content, Form $form = null, $variables = [])
    {
        if (! ($content instanceof ViewModel)) {
            $sectionParams = array_merge(
                [
                    'form' => $form
                ],
                $variables
            );

            if (!empty($variables['title'])) {
                $title = $variables['title'];
            } else {
                $title = 'lva.section.title.' . $content;
                if ($content == 'operating_centres') {
                    $applicationData = $this->getApplicationData($this->getApplicationId());
                    if ($applicationData['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV) {
                        $title .= '.lgv';
                    }
                }
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
        $filter = $this->stringHelper;

        $sections = [
            'overview' => ['class' => 'no-background', 'route' => 'lva-application', 'enabled' => true]
        ];

        $status = $applicationCompletion['status']['id'];
        // if status is valid then only show Overview section
        if ($status === RefData::APPLICATION_STATUS_VALID) {
            return $sections;
        }

        $isPsv = $applicationCompletion['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV;
        $isLgv = $applicationCompletion['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV;

        $accessibleSections = $this->setEnabledAndCompleteFlagOnSections(
            $this->getAccessibleSections(false),
            $applicationStatuses
        );

        foreach ($accessibleSections as $section => $settings) {
            $alias = $section;
            if ($section == 'community_licences' && $isPsv) {
                $alias = $section . '.psv';
            } elseif ($section == 'operating_centres' && $isLgv) {
                $alias = $section . '.lgv';
            }

            $statusIndex = lcfirst($filter->underscoreToCamel($section)) . 'Status';

            $class = '';
            switch ($applicationStatuses[$statusIndex]) {
                case RefData::APPLICATION_COMPLETION_STATUS_COMPLETE:
                    $class = 'complete';
                    break;
                case RefData::APPLICATION_COMPLETION_STATUS_INCOMPLETE:
                    $class = 'incomplete';
                    break;
            }

            $sections[$section] = array_merge(
                $settings,
                [
                    'class' => $class,
                    'route' => 'lva-application/' . $section,
                    'alias' => $alias
                ]
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

        if (!$response->isOk()) {
            throw new \RuntimeException('Failed to get Application data');
        }

        return $response->getResult();
    }
}

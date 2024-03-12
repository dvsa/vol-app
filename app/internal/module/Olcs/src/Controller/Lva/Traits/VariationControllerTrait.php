<?php

/**
 * INTERNAL Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Lva\Traits\CommonVariationControllerTrait;
use Common\RefData;
use Common\View\Model\Section;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * INTERNAL Abstract Variation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait VariationControllerTrait
{
    use ApplicationControllerTrait, CommonVariationControllerTrait {
        CommonVariationControllerTrait::preDispatch insteadof ApplicationControllerTrait;
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
     * @param string|ViewModel   $content   content
     * @param \Laminas\Form\Form $form      form
     * @param array              $variables variables
     *
     * @return \Laminas\View\Model\ViewModel
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

            if (!empty($variables['title'])) {
                $title = $variables['title'];
            } else {
                $title = 'lva.section.title.' . $content;
                if ($content == 'community_licences') {
                    $response = $this->handleQuery(
                        Licence::create(['id' => $this->getLicenceId()])
                    );
                    $licence = $response->getResult();

                    if ($licence['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV) {
                        $title .= '.psv';
                    }
                } elseif ($content == 'operating_centres') {
                    $applicationData = $this->getApplicationData($this->getApplicationId());
                    if ($applicationData['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV) {
                        $title .= '.lgv';
                    }
                }
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
        $filter = $this->stringHelper;

        $sections = [
            'overview' => ['class' => 'no-background', 'route' => 'lva-variation']
        ];

        $status = $applicationData['status']['id'];
        // if status is valid then only show Overview section
        if ($status === \Common\RefData::APPLICATION_STATUS_VALID) {
            return $sections;
        }

        $isPsv = $applicationData['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV;
        $isLgv = $applicationData['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV;

        $accessibleSections = $this->getAccessibleSections(false);

        foreach ($accessibleSections as $section => $settings) {
            $alias = $section;
            if ($section == 'community_licences' && $isPsv) {
                $alias = $section . '.psv';
            } elseif ($section == 'operating_centres' && $isLgv) {
                $alias = $section . '.lgv';
            }

            $statusIndex = lcfirst($filter->underscoreToCamel($section)) . 'Status';

            $class = '';
            switch ($variationStatuses[$statusIndex]) {
                case RefData::VARIATION_STATUS_UPDATED:
                    $class = 'edited';
                    break;
                case RefData::VARIATION_STATUS_REQUIRES_ATTENTION:
                    $class = 'incomplete';
                    break;
            }

            $sections[$section] = array_merge(
                $settings,
                [
                    'class' => $class,
                    'route' => 'lva-variation/' . $section,
                    'alias' => $alias
                ]
            );
        }

        return $sections;
    }
}

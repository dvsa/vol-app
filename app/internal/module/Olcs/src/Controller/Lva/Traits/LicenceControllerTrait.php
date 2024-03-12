<?php

/**
 * Internal Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Traits;

use Common\RefData;
use Laminas\Form\Form;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;

/**
 * Internal Abstract Licence Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
trait LicenceControllerTrait
{
    use InternalControllerTrait;
    use Traits\LicenceControllerTrait;

    private $searchForm;

    /**
     * Hook into the dispatch before the controller action is executed
     *
     * @return null|Laminas\Http\Response
     */
    protected function preDispatch()
    {
        $licenceId = $this->getLicenceId();

        return $this->checkForRedirect($licenceId);
    }

    /**
     * Get licence id
     *
     * @param int $applicationId applicationId
     *
     * @return int
     */
    protected function getLicenceId($applicationId = null)
    {
        return $this->getIdentifier();
    }

    /**
     * render Page
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
     * get Method Left
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
        $left->setTemplate('sections/licence/partials/left');

        return $left;
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
        $this->attachCurrentMessages();

        if ($form instanceof Form && !$form->getOption('readonly')) {
            if ($form->has('form-actions')) {
                $form->get('form-actions')->remove('saveAndContinue');
            }
        }

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

                if ($content == 'community_licences') {
                    $licence = $this->getLicence();
                    if ($licence['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV) {
                        $title .= '.psv';
                    }
                } elseif ($content == 'operating_centres') {
                    $licence = $this->getLicence();
                    if (
                        isset($licence['vehicleType']['id'])
                        && $licence['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV
                    ) {
                        $title .= '.lgv';
                    }
                }
            }

            $content = new ViewModel($sectionParams);
            $content->setTemplate('sections/lva/lva-details');

            return $this->renderPage($content, $title, $variables);
        }

        return $this->renderPage($content, $content->getVariable('title'), $variables);
    }

    /**
     * Gets the search form for the header, it is cached on the object so that the search query is maintained
     *
     * @return Laminas\Session\Container
     */
    public function getSearchForm()
    {
        if ($this->searchForm === null) {
            $this->searchForm = $this->formHelper
                ->createForm('HeaderSearch', false, false);

            $container = new Container('search');
            $this->searchForm->bind($container);
        }

        return $this->searchForm;
    }

    /**
     * Get the sections for the view
     *
     * @return array
     */
    protected function getSectionsForView()
    {
        $sections = [
            'overview' => ['route' => 'lva-licence']
        ];

        $licence = $this->getLicence();
        $isPsv = ($licence['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_PSV);
        $isLgv = isset($licence['vehicleType']['id']) && $licence['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV;

        foreach ($this->getAccessibleSections() as $section) {
            $sectionKey = $section;
            if ($isPsv && $sectionKey == 'community_licences') {
                $sectionKey = 'community_licences.psv';
            } elseif ($isLgv && $sectionKey == 'operating_centres') {
                $sectionKey = 'operating_centres.lgv';
            }

            $sections[$sectionKey] = ['route' => 'lva-licence/' . $section];
        }

        return $sections;
    }

    /**
     * Complete a section and potentially redirect to the next
     * one depending on the user's choice
     *
     * @param string $section section
     *
     * @return \Laminas\Http\Response
     */
    protected function completeSection($section)
    {
        return $this->goToOverviewAfterSave($this->getLicenceId());
    }
}

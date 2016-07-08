<?php

/**
 * Internal Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\RefData;
use Olcs\Controller\Traits;
use Zend\Session\Container;

/**
 * Internal Abstract Licence Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
trait LicenceControllerTrait
{
    use InternalControllerTrait,
        // @TODO: the LVA trait importing the old, generic licence trait
        // should be a temporary measure; they need consolidating into one
        Traits\LicenceControllerTrait;

    private $searchForm;

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        $licenceId = $this->getLicenceId();

        return $this->checkForRedirect($licenceId);
    }

    /**
     * Get licence id
     *
     * @return int
     */
    protected function getLicenceId($applicationId = null)
    {
        return $this->getIdentifier();
    }

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
     * @param string|ViewModel $content
     * @param \Zend\Form\Form $form
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function render($content, Form $form = null, $variables = [])
    {
        $this->attachCurrentMessages();

        if ($form instanceof Form && !$form->getOption('readonly')) {
            $form->get('form-actions')->remove('saveAndContinue');
        }

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

            return $this->renderPage($content, $title, $variables);
        }

        return $this->renderPage($content, $content->getVariable('title'), $variables);
    }

    /**
     * Gets the search form for the header, it is cached on the object so that the search query is maintained
     */
    public function getSearchForm()
    {
        if ($this->searchForm === null) {
            $this->searchForm = $this->getServiceLocator()
                ->get('Helper\Form')
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
        $sections = array(
            'overview' => array('route' => 'lva-licence')
        );

        foreach ($this->getAccessibleSections() as $section) {

            $sections[$section] = array('route' => 'lva-licence/' . $section);
        }

        return $sections;
    }

    /**
     * Complete a section and potentially redirect to the next
     * one depending on the user's choice
     *
     * @return \Zend\Http\Response
     */
    protected function completeSection($section)
    {
        $this->addSectionUpdatedMessage($section);

        return $this->goToOverviewAfterSave($this->getLicenceId());
    }
}

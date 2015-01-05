<?php

/**
 * Internal Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\View\Model\Section;
use Olcs\View\Model\Licence\SectionLayout;
use Olcs\View\Model\Licence\Layout;
use Olcs\View\Model\Licence\LicenceLayout;
use Common\Service\Entity\LicenceEntityService;
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
    protected function getLicenceId()
    {
        return $this->getIdentifier();
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

        if ($form instanceof Form) {
            $form->get('form-actions')->remove('saveAndContinue');
        }

        if (! ($content instanceof ViewModel)) {
            $sectionParams = array_merge(
                array('title' => 'lva.section.title.' . $content, 'form' => $form),
                $variables
            );

            $content = new Section($sectionParams);
        }

        $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $sectionLayout = new SectionLayout(
            array_merge(
                array(
                    'sections'     => $this->getSectionsForView(),
                    'currentRoute' => $routeName,
                    'lvaId'        => $this->getIdentifier()
                ),
                $variables
            )
        );

        $sectionLayout->addChild($content, 'content');

        $licenceLayout = new LicenceLayout();

        $licenceLayout->addChild($sectionLayout, 'content');

        $params = $this->getHeaderParams();

        $licenceLayout->setVariable(
            'markers',
            $this->setupMarkers($this->getLicence())
        );
        $params = array_merge($params, $variables);

        $view = new Layout($licenceLayout, $params);
        $view->setVariable('searchForm', $this->getSearchForm());
        return $view;
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
     * Get headers params
     *
     * @return array
     */
    protected function getHeaderParams()
    {
        $data = $this->getServiceLocator()->get('Entity\Licence')->getHeaderParams($this->getLicenceId());

        if ($data['goodsOrPsv']['id'] === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        return array(
            'licNo' => $data['licNo'],
            'companyName' => $data['organisation']['name'],
            'description' => $data['status']['description']
        );
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

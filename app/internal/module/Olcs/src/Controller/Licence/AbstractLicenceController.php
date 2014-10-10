<?php

/**
 * Internal Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Zend\View\Model\ViewModel;
use Olcs\View\Model\Licence\SectionLayout;
use Olcs\View\Model\Licence\Layout;
use Olcs\View\Model\Licence\LicenceLayout;
use Olcs\Controller\AbstractInternalController;

/**
 * Internal Abstract Licence Controller
 *
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
abstract class AbstractLicenceController extends AbstractInternalController
{
    /**
     * Lva
     *
     * @var string
     */
    protected $lva = 'licence';

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        $licenceId = $this->getLicenceId();

        $this->getEvent()->getRouteMatch()->setParam('licence', $licenceId);

        return $this->checkForRedirect($licenceId);
    }

    /**
     * Get licence id
     *
     * @return int
     */
    protected function getLicenceId()
    {
        return $this->params('id');
    }

    /**
     * Get type of licence data
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        return $this->getEntityService('Licence')->getTypeOfLicenceData($this->getLicenceId());
    }

    /**
     * Render the section
     *
     * @param ViewModel $content
     */
    protected function render(ViewModel $content)
    {
        $routeName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

        $sectionLayout = new SectionLayout(
            array('sections' => $this->getSectionsForView(), 'currentRoute' => $routeName)
        );

        $sectionLayout->addChild($content, 'content');

        $licenceLayout = new LicenceLayout();

        $licenceLayout->addChild($this->getQuickActions(), 'actions');
        $licenceLayout->addChild($sectionLayout, 'content');

        $params = $this->getHeaderParams();

        return new Layout($licenceLayout, $params);
    }

    /**
     * Get headers params
     *
     * @return array
     */
    protected function getHeaderParams()
    {
        $data = $this->getEntityService('Licence')->getHeaderParams($this->getLicenceId());

        return array(
            'licNo' => $data['licNo'],
            'companyName' => $data['organisation']['name'],
            'status' => $data['status']['id']
        );
    }

    /**
     * Quick action view model
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function getQuickActions()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('licence/quick-actions');

        return $viewModel;
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
}

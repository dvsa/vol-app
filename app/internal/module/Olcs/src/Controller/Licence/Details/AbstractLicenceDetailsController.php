<?php

/**
 * Abstract LicenceDetails Controller
 */
namespace Olcs\Controller\Licence\Details;

use Olcs\Helper\LicenceDetailsHelper;
use Olcs\Controller\Traits\LicenceControllerTrait;
use Zend\Navigation\Navigation;
use Common\Controller\AbstractSectionController;
use Common\Form\Fieldsets\Custom\SectionButtons;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
use Common\Controller\Traits\GenericLicenceSection;

/**
 * Abstract LicenceDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractLicenceDetailsController extends AbstractSectionController
{
    use LicenceControllerTrait,
        GenericLicenceSection;

    const LICENCE_CATEGORY_GOODS_VEHICLE = 'lcat_gv';
    const LICENCE_CATEGORY_PSV = 'lcat_psv';

    /**
     * Licence types keys
     */
    const LICENCE_TYPE_RESTRICTED = 'ltyp_r';
    const LICENCE_TYPE_STANDARD_INTERNATIONAL = 'ltyp_si';
    const LICENCE_TYPE_STANDARD_NATIONAL = 'ltyp_sn';
    const LICENCE_TYPE_SPECIAL_RESTRICTED = 'ltyp_sr';

    /**
     * Holds the current section
     *
     * @var string
     */
    protected $section = '';

    /**
     * Holds the licence details helper
     *
     * @var \Olcs\Helper\LicenceDetailsHelper
     */
    protected $licenceDetailsHelper;

    /**
     * Holds the identifier name
     *
     * @var string
     */
    protected $identifierName = 'licence';

    /**
     * Get the licence details helper
     *
     * @return \Olcs\Helper\LicenceDetailsHelper
     */
    protected function getLicenceDetailsHelper()
    {
        if (empty($this->licenceDetailsHelper)) {
            $this->licenceDetailsHelper = new LicenceDetailsHelper();
        }

        return $this->licenceDetailsHelper;
    }

    /**
     * Extend the render view method
     *
     * @param type $view
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $this->pageLayout = 'licence';

        $variables = array(
            'navigation' => $this->getSubNavigation(),
            'section' => $this->section
        );

        $layout = $this->getViewWithLicence($variables);
        $layout->setTemplate('licence/details/layout');

        $this->maybeAddScripts($layout);

        $layout->addChild($view, 'content');

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }

    /**
     * Render section
     *
     * @return array
     */
    protected function renderSection()
    {
        $redirect = $this->checkForRedirect();

        if ($redirect instanceof Response || $redirect instanceof ViewModel) {

            return $redirect;
        }

        $form = $this->getNewForm();

        $response = $this->getCaughtResponse();

        if ($response !== null) {
            return $response;
        }

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('partials/section');

        $this->maybeAddTable($view);

        return $this->renderView($view);
    }

    /**
     * Get sub navigation
     */
    protected function getSubNavigation()
    {
        $licence = $this->getLicence();

        $navigationConfig = $this->getLicenceDetailsHelper()->getNavigation(
            $licence['id'],
            $licence['goodsOrPsv']['id'],
            $licence['licenceType']['id'],
            $this->section
        );

        $navigation = new Navigation($navigationConfig);

        $router = $this->getServiceLocator()->get('router');

        foreach ($navigation->getPages() as $page) {
            $page->setRouter($router);
        }

        return $navigation;
    }

    /**
     * Generic form alterations for the licence section
     *
     * @param Form $form
     */
    protected function alterForm($form)
    {
        $form->remove('form-actions');

        $form->add(new SectionButtons());

        return $form;
    }


    /**
     * Get the licence data
     *
     * @return array
     */
    protected function getLicenceData()
    {
        return $this->doGetLicenceData();
    }

    /**
     * Get licence if
     *
     * @return int
     */
    protected function getLicenceId()
    {
        return $this->getIdentifier();
    }
}

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

/**
 * Abstract LicenceDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractLicenceDetailsController extends AbstractSectionController
{
    use LicenceControllerTrait;

    const LICENCE_CATEGORY_GOODS_VEHICLE = 'lcat_gv';
    const LICENCE_CATEGORY_PSV = 'lcat_psv';

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
     * Caches the licence data
     *
     * @var array
     */
    private $licenceData;

    /**
     * Holds the licenceDataBundle
     *
     * @var array
     */
    public static $licenceDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'niFlag'
        ),
        'children' => array(
            'goodsOrPsv' => array(
                'properties' => array(
                    'id'
                )
            ),
            'licenceType' => array(
                'properties' => array(
                    'id'
                )
            ),
            'organisation' => array(
                'children' => array(
                    'type' => array(
                        'properties' => array(
                            'id'
                        )
                    )
                )
            )
        )
    );

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
        $view->setTemplate('partials/form');

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
        if (empty($this->licenceData)) {

            $this->licenceData = $this->makeRestCall(
                'Licence',
                'GET',
                array('id' => $this->getIdentifier()),
                self::$licenceDataBundle
            );
        }

        return $this->licenceData;
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

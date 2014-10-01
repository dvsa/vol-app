<?php

/**
 * Abstract LicenceDetails Controller
 *
 * External
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Licence\Details\AbstractLicenceDetailsController as CommonAbstractLicenceDetailsController;
use Zend\View\Model\ViewModel;

/**
 * Abstract LicenceDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractLicenceDetailsController extends CommonAbstractLicenceDetailsController
{
    /**
     * Extend the render view method
     *
     * @param type $view
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $licence = $this->getLicenceData();

        $container = null;
        if (!empty($this->navigationItem)) {
            $container = $this->getServiceLocator()->get('Navigation')->findOneBy('id', $this->navigationItem);
        }

        // Section layout
        $sectionLayout = new ViewModel(array('subNav' => $container));
        $sectionLayout->setTemplate('licence/details/layout');

        $this->maybeAddScripts($sectionLayout);

        $sectionLayout->addChild($view, 'content');

        // Licence Layout
        $licenceLayout = new ViewModel(
            array(
                'pageTitle' => $licence['licNo'],
                'pageSubTitle' => 'Operator licence'
            )
        );
        $licenceLayout->setTemplate('layout/licence');

        $licenceLayout->addChild($sectionLayout, 'content');

        return $licenceLayout;
    }

    /**
     * Gets the licence by ID.
     *
     * @param integer $id
     * @return array
     */
    protected function getLicence($id = null)
    {
        return $this->getLicenceData();
    }
}

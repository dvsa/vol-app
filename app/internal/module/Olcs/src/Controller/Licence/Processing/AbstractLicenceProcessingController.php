<?php

/**
 * Abstract Licence Processing Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\Licence\LicenceController;
use Olcs\Helper\LicenceProcessingHelper;
use Olcs\Controller\Traits\ProcessingControllerTrait;

/**
 * Abstract Licence Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractLicenceProcessingController extends LicenceController
{
    use ProcessingControllerTrait;

    /**
     * Anything using renderView in this section will
     * inherit this layout
     *
     * @var string
     */
    protected $pageLayout = 'licence-section';

    protected $helperClass = '\Olcs\Helper\LicenceProcessingHelper';

    protected function getNavigationConfig()
    {
        $licence = $this->getLicence();

        return $this->getProcessingHelper()->getNavigation(
            $licence['id'],
            $this->section
        );
    }

    protected function getProcessingLayout($view, $variables)
    {
        $layout = $this->getViewWithLicence(
            array_merge($variables, (array)$view->getVariables())
        );
        $layout->setTemplate('layout/processing-subsection');

        $layout->addChild($view, 'content');

        return $layout;

    }
}

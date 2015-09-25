<?php

/**
 * Abstract Licence Processing Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Licence\LicenceController;
use Olcs\Controller\Traits\ProcessingControllerTrait;
use Zend\View\Model\ViewModel;

/**
 * Abstract Licence Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractLicenceProcessingController extends LicenceController implements LeftViewProvider
{
    use ProcessingControllerTrait;

    protected $helperClass = '\Olcs\Helper\LicenceProcessingHelper';

    protected function getNavigationConfig()
    {
        $licence = $this->getLicence();

        return $this->getProcessingHelper()->getNavigation(
            $licence['id'],
            $this->section
        );
    }

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}

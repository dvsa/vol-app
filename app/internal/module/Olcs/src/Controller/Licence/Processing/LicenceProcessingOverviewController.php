<?php

/**
 * Overview Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Zend\View\Model\ViewModel;

/**
 * Licence Processing Overview Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingOverviewController extends AbstractLicenceProcessingController
{
    protected $section = 'overview';

    public function indexAction()
    {
        //this is just a placeholder, links to only page in processing for now, which is notes
        return $this->redirectToRoute('licence/processing/tasks', [], [], true);
    }
}

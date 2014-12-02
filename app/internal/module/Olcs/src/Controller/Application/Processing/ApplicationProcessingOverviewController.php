<?php

/**
 * Overview Controller
 */
namespace Olcs\Controller\Application\Processing;

use Zend\View\Model\ViewModel;

/**
 * Application Processing Overview Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingOverviewController extends AbstractApplicationProcessingController
{
    protected $section = 'overview';

    public function indexAction()
    {
        //this is just a placeholder, links to only page in processing for now, which is tasks
        return $this->redirectToRoute('lva-application/processing/tasks', [], [], true);
    }
}

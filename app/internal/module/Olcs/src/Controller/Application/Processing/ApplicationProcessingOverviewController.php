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
        // redirect to the tasks page as we don't have an actual overview
        return $this->redirectToRoute('lva-application/processing/tasks', [], [], true);
    }
}

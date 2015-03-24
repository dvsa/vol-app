<?php


/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Olcs\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

class BatchController extends AbstractConsoleController
{
    public function licenceStatusAction()
    {
        $verbose = $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');

        /* @var $batchService \Olcs\Service\Processing\BatchLicenceStatusProcessingService */
        $batchService = $this->getServiceLocator()->get('Olcs\Service\Processing\BatchLicenceStatus');
        if ($verbose) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->processToRevokeCurtailSuspend();
        $batchService->processToValid();
    }
}

<?php

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli\Controller;

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
        $batchService = $this->getServiceLocator()->get('BatchLicenceStatus');
        if ($verbose) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->processToRevokeCurtailSuspend();
        $batchService->processToValid();
    }

    public function inspectionRequestEmailAction()
    {
        $verbose = $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');

        /* @var $batchService \Olcs\Service\Processing\BatchInspectionRequestEmailProcessingService */
        $batchService = $this->getServiceLocator()->get('BatchInspectionRequestEmail');
        if ($verbose) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->process();
    }
}

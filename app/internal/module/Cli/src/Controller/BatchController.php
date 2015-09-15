<?php

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\View\Model\ConsoleModel;
use Common\Service\Entity\QueueEntityService;

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BatchController extends AbstractConsoleController
{
    /**
     * @return void
     */
    public function licenceStatusAction()
    {
        /* @var $batchService \Cli\Service\Processing\BatchLicenceStatusProcessingService */
        $batchService = $this->getService('BatchLicenceStatus');
        $batchService->processToRevokeCurtailSuspend();
        $batchService->processToValid();
    }

    /**
     * @return Zend\View\Model\ConsoleModel
     */
    public function inspectionRequestEmailAction()
    {
        /* @var $batchService \Cli\Service\Processing\BatchInspectionRequestEmailProcessingService */
        $batchService = $this->getService('BatchInspectionRequestEmail');
        $result = $batchService->process();

        return $this->handleExitStatus($result);
    }

    /**
     * @return void
     */
    public function continuationNotSoughtAction()
    {
        $dryRun = $this->getRequest()->getParam('dryrun') || $this->getRequest()->getParam('d');

        /* @var $batchService \Cli\Service\Processing\ContinuationNotSought */
        $batchService = $this->getService('BatchContinuationNotSought');
        $batchService->process(['dryRun' => $dryRun]);

        // send the email
        if (!$dryRun) {
            $this->getServiceLocator()->get('Email\ContinuationNotSought')->send();
        }
    }

    /**
     * Wrapper function to get service and set the console adapter on it if
     * we're in verbose mode
     *
     * @param string $name service name
     * @return Cli\Service\Processing\AbstractBatchProcessingService
     */
    private function getService($name)
    {
        $batchService = $this->getServiceLocator()->get($name);
        if ($this->isVerbose()) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        return $batchService;
    }

    /**
     * @return boolean
     */
    private function isVerbose()
    {
        return $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');
    }

    /**
     * Using this method ensures the calling CLI environment gets an appropriate
     * exit code from the process.
     *
     * @param int $result exit code, should be non-zero if there was an error
     * @return Zend\View\Model\ConsoleModel
     */
    private function handleExitStatus($result)
    {
        $model = new ConsoleModel();
        $model->setErrorLevel($result);
        return $model;
    }
}

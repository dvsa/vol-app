<?php

namespace Olcs\Controller\IrhpPermits;

use Olcs\Controller\AbstractHistoryController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;

class IrhpApplicationProcessingHistoryController extends AbstractHistoryController implements IrhpApplicationControllerInterface
{
    protected $navigationId = 'licence_irhp_applications_processing_event-history';
    protected $listVars = ['irhpApplication' => 'irhpAppId'];
    protected $itemParams = ['irhpAppId', 'id' => 'id'];
}

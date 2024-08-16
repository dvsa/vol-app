<?php

namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\AbstractHistoryController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;

class HistoryController extends AbstractHistoryController implements LicenceControllerInterface
{
    protected $navigationId = 'licence_processing_event-history';
    protected $listVars = ['licence'];
    protected $itemParams = ['licence', 'id' => 'id'];
}

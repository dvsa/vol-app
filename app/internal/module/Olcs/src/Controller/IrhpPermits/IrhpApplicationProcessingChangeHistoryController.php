<?php

namespace Olcs\Controller\IrhpPermits;

use Olcs\Controller\AbstractHistoryController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;

/**
 * Irhp Application change history controller
 */
class IrhpApplicationProcessingChangeHistoryController extends AbstractHistoryController implements IrhpApplicationControllerInterface
{
    use ShowIrhpApplicationNavigationTrait;

    protected $navigationId = 'licence_irhp_applications_processing_change-history';
    protected $listVars = ['licence'];
    protected $itemParams = ['licence', 'id' => 'id'];
}

<?php

namespace Olcs\Controller\IrhpPermits;

use Olcs\Controller\AbstractHistoryController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;

/**
 * Permits change history controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ChangeHistoryController extends AbstractHistoryController implements IrhpApplicationControllerInterface
{
    protected $navigationId = 'licence_irhp_permits_processing_change-history';
    protected $listVars = ['licence'];
    protected $itemParams = ['licence', 'id' => 'id'];
}

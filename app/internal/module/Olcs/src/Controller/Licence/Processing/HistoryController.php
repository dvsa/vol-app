<?php
/**
 * Licence History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\AbstractHistoryController;

/**
 * Licence History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HistoryController extends AbstractHistoryController implements LicenceControllerInterface
{
    protected $navigationId = 'licence_processing_event-history';
    protected $listVars = ['licence'];
    protected $itemParams = ['licence', 'id' => 'id'];
}

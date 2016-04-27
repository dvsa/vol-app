<?php
/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Controller\AbstractHistoryController;

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HistoryController extends AbstractHistoryController implements TransportManagerControllerInterface
{
    protected $navigationId = 'transport_manager_processing_event-history';
    protected $listVars = ['transportManager'];
    protected $itemParams = ['transportManager', 'id' => 'id'];
}

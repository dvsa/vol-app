<?php
/**
 * Application History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\AbstractHistoryController;

/**
 * Application History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HistoryController extends AbstractHistoryController implements ApplicationControllerInterface
{
    protected $navigationId = 'application_processing_history';
    protected $listVars = ['application'];
    protected $itemParams = ['application', 'id' => 'id'];
}

<?php
/**
 * Application History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Processing;

use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\AbstractHistoryController;

/**
 * Application History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HistoryController extends AbstractHistoryController implements CaseControllerInterface
{
    protected $itemParams = ['case', 'id' => 'id'];
    protected $navigationId = 'case_processing_history';
    protected $listVars = ['case'];
}

<?php

/**
 * BusReg History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Dvsa\Olcs\Transfer\Query\Bus\HistoryList as BusRegHistoryList;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\AbstractHistoryController;

/**
 * BusReg History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HistoryController extends AbstractHistoryController implements BusRegControllerInterface
{
    protected $navigationId = 'licence_bus_processing';
    protected $listVars = ['busReg' => 'busRegId'];
    protected $listDto = BusRegHistoryList::class;
    protected $itemParams = ['busReg', 'id' => 'id'];
}

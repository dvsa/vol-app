<?php

/**
 * Class AnnualTestHistoryController
 */
namespace Olcs\Controller\Cases\AnnualTestHistory;

use Dvsa\Olcs\Transfer\Query\Cases\AnnualTestHistory as AnnualTestHistoryQuery;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateAnnualTestHistory as UpdateAnnualTestHistoryCommand;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\AnnualTestHistory as AnnualTestHistoryMapper;
use Olcs\Form\Model\Form\AnnualTestHistory as AnnualTestHistoryForm;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Zend\View\Model\ViewModel;

/**
 * Class AnnualTestHistoryController
 */
class AnnualTestHistoryController extends AbstractInternalController implements
    CaseControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_annual_test_history';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $itemParams = ['id' => 'case', 'case' => 'case'];
    protected $itemDto = AnnualTestHistoryQuery::class;
    protected $updateCommand = UpdateAnnualTestHistoryCommand::class;
    protected $mapperClass = AnnualTestHistoryMapper::class;
    protected $formClass = AnnualTestHistoryForm::class;
    protected $editContentTitle = 'Annual test history';

    /**
     * get Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }
}

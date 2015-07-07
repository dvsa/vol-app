<?php

/**
 * Class AnnualTestHistoryController
 */
namespace Olcs\Controller\Cases\AnnualTestHistory;

use Dvsa\Olcs\Transfer\Query\Cases\AnnualTestHistory as AnnualTestHistoryQuery;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateAnnualTestHistory as UpdateAnnualTestHistoryCommand;
use Olcs\Data\Mapper\AnnualTestHistory as AnnualTestHistoryMapper;
use Olcs\Form\Model\Form\AnnualTestHistory as AnnualTestHistoryForm;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

/**
 * Class AnnualTestHistoryController
 */
class AnnualTestHistoryController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
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

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }
}

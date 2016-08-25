<?php

namespace Olcs\Controller\Cases\Conviction;

use Dvsa\Olcs\Transfer\Query\Cases\LegacyOffence;
use Dvsa\Olcs\Transfer\Query\Cases\LegacyOffenceList;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Class LegacyOffenceController
 */
class LegacyOffenceController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_legacy_offence_details';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'legacyOffences';
    protected $listDto = LegacyOffenceList::class;
    protected $listVars = ['case'];

    /**
     * Build left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/cases/pages/offence';
    protected $detailsViewPlaceholderName = 'details';
    protected $detailsContentTitle = 'Legacy offence details';
    protected $itemDto = LegacyOffence::class;
    protected $itemParams = ['case', 'id'];
}

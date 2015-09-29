<?php
/**
 * Licence Bus Reg Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList as ListDto;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Form\Model\Form\BusRegList as FilterForm;

/**
 * Licence Bus Reg Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BusRegistrationController extends AbstractInternalController implements LicenceControllerInterface,
    PageLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_bus';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'layout/bus-registrations-list';
    protected $defaultTableSortField = 'regNo';
    protected $tableName = 'busreg';
    protected $listDto = ListDto::class;
    protected $listVars = [
        'licId' => 'licence'
    ];
    protected $filterForm = FilterForm::class;

    public function getPageLayout()
    {
        return 'layout/licence-section';
    }

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array(
        'indexAction' => ['forms/filter', 'table-actions']
    );
}

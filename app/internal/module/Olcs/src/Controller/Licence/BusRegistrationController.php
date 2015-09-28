<?php
/**
 * Licence Bus Reg Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractInternalController as AbstractController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList as ListDto;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Form\Model\Form\BusRegList as FilterForm;

use Olcs\Listener\CrudListener;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Olcs\Mvc\Controller\ParameterProvider\ParameterProviderInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent as MvcEvent;
use Olcs\Logging\Log\ZendLogPsr3Adapter as Logger;

/**
 * Licence Bus Reg Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class BusRegistrationController extends AbstractController implements LicenceControllerInterface,
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

    public function getPageInnerLayout()
    {
        return 'layout/licence-details-subsection';
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

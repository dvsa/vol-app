<?php

/**
 * Case Serious Infringement Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\SeriousInfringement;

use Olcs\Controller\CrudAbstract;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;
use Zend\View\Model\ViewModel;

/**
 * Case Serious Infringement Controller
 *
 * @todo not sure if this is needed. Don't seem to be able to access this through the app. Spoke to John Instone and he
 * doesn't think this is needed
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SeriousInfringementController extends CrudAbstract implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name in the URL
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Identifier key in the database
     *
     * @var string
     */
    protected $identifierKey = 'id';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'Si';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'Si';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_serious_infringement';

    protected $placeholderName = 'nonPi';

    protected $detailsView = '';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case'
    ];

    /**
     * Contains the name of the view placeholder for the table.
     *
     * @var string
     */
    protected $tableViewPlaceholderName = 'table';

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
            )
        )
    );

    /**
     * Holds the isAction
     *
     * @var boolean
    */
    protected $isAction = false;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'SeriousInfringement';

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = [
        'children' => [
            'case' => [],
            'memberStateCode' => [],
            'siCategory' => [],
            'siCategoryType' => []
        ]
    ];

    protected $inlineScripts = [];

    protected $addContentTitle = 'Add serious infringement';
    protected $editContentTitle = 'Edit serious infringement';

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');
        return $view;
    }

    public function indexAction()
    {
        $params = $this->getTableParams();

        $data = $this->loadListData($params);

        if (isset($data['Results'][0]['id'])) {

            $id = $data['Results'][0]['id'];

            return $this->redirect()->toRoute(
                'serious_infringement', ['action' => 'edit', 'id' => $id], [], true
            );
        }

        return $this->redirect()->toRoute('serious_infringement', ['action' => 'add'], [], true);
    }
}

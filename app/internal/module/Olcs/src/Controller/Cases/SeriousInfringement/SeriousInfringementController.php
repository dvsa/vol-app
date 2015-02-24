<?php
/**
 * Case Serious Infringement Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\SeriousInfringement;

// Olcs
use Olcs\Controller\CrudAbstract;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Traits as ControllerTraits;

use Zend\View\Model\ViewModel;

/**
 * Case Non Public Inquiry Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SeriousInfringementController extends CrudAbstract implements CaseControllerInterface
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
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    /**
     * For most case crud controllers, we use the case/inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

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
        'properties' => 'ALL',
        'children' => [
            'case' => [
                'properties' => 'ALL',
            ],
            'memberStateCode' => [
                'properties' => 'ALL',
            ],
            'siCategory' => [
                'properties' => 'ALL',
            ],
            'siCategoryType' => [
                'properties' => 'ALL',
            ]
        ]
    ];

    protected $inlineScripts = [];

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

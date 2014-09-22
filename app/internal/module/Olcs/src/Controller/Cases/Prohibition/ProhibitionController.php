<?php

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Prohibition;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

    /**
     * Case Prohibition Controller
     *
     * @author Ian Lindsay <ian@hemera-business-services.co.uk>
     */
class ProhibitionController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'prohibition';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'prohibition';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = 'prohibitionNote';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'prohibition';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * For most case crud controllers, we use the case/inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Prohibition';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_prohibitions';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
    ];

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
                'base',
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
     * Holds the Data Bundle
     *
     * @var array
    */
    protected $dataBundle = array(
        'children' => array(
            'case' => array(
                'properties' => array(
                    'id'
                )
            ),
            'prohibitionType' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            )
        )
    );

    /*public function addAction()
    {
        $this->checkTableCrud();
        parent::addAction();
    }

    public function editAction(){
        $this->checkTableCrud();

        $this->getViewHelperManager()->get('placeholder')->getContainer('table')->set(
            $this->generateProhibitionDefectTable($this->getFromRoute('prohibition'))
        );

        parent::editAction();

        $view = $this->getView();

        $view->setTemplate('prohibition/edit');

        return $this->renderView($view);
    }

    public function deleteAction()
    {
        $this->checkTableCrud();
        parent::deleteAction();
    }

    public function checkTableCrud()
    {
        $action = $this->fromPost('action');
        $defect = $this->fromPost('id');
//echo $action . ' ' . $defect;
        //die();
        switch ($action) {
            case 'Add':
                return $this->redirectToRoute('case_prohibition_defect', ['action' => 'add'], [], true);
            case 'Edit':
                return $this->redirectToRoute(
                    'case_prohibition_defect',
                    ['action' => 'edit', 'id' => $defect],
                    [],
                    true
                );
            case 'Delete':
                return $this->redirectToRoute(
                    'case_prohibition_defect',
                    ['action' => 'delete', 'id' => $defect],
                    [],
                    true
                );
        }
    }

    /**
     * Gets a table of defects for the prohibition
     *
     * @param int $prohibitionId
     * @return string
     */
   /* private function generateProhibitionDefectTable($prohibitionId)
    {
        $bundle = [
            'children' => [
                'prohibition' => [
                    'properties' => [
                        'id'
                    ]
                ]

            ]
        ];

        $results = $this->makeRestCall(
            'ProhibitionDefect',
            'GET',
            array(
                'prohibition' => $prohibitionId,
                'bundle' => json_encode($bundle)
            )
        );

        if (!$results['Count']) {
            $results = array();
        }

        return $this->getTable('prohibitionDefect', $results);
    }*/

    /**
     * If we wanted we could return a view, but we don't need to
     *
     * @return void
     */
    public function detailsAction()
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('prohibition')->set(
            $this->loadCurrent()
        );
    }
}

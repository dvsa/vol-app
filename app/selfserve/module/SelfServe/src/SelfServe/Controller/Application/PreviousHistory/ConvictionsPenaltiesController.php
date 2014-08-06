<?php

/**
 * ConvictionsPenalties Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

/**
 * ConvictionsPenalties Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ConvictionsPenaltiesController extends PreviousHistoryController
{

    /**
     * Form tables name
     *
     * @var string
     */
    protected $formTables = array(
        'table' => 'criminalconvictions'
    );

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data',
                'convictionsConfirmation'
            ),
        )
    );

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'prevConviction',
            'convictionsConfirmation',
        )
    );

    /**
     * Holds the action service
     *
     * @var string
     */
    protected $actionService = 'Conviction';

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Save method
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($validData, $service = null)
    {
        $validData['id'] = $this->getIdentifier();
        $validData['prevConviction'] = ($validData['prevConviction'] == 'Y') ?
            true : (($validData['prevConviction'] == 'N') ? false : null);
        $validData['convictionsConfirmation'] = $validData['convictionsConfirmation'][0];
        parent::save($validData, 'Application');
    }

    /**
     * Action save
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $applicationId = $this->getIdentifier();
        $data['application'] = $applicationId;
        parent::actionSave($data, 'Conviction');

    }

    /**
     * Retrieve the relevant table data as we want to render it on the review summary page
     * Note that as with most controllers this is the same data we want to render on the
     * normal form page, hence why getFormTableData (declared later) simply wraps this
     */
    public static function getSummaryTableData($applicationId, $context, $tableName)
    {
        $bundle = array(
            'properties' => array(
                'id',
                'personTitle',
                'personFirstname',
                'personLastname',
                'dateOfConviction',
                'convictionNotes',
                'courtFpm',
                'penalty'
            ),
        );

        $data = $context->makeRestCall(
            'Conviction',
            'GET',
            array('application' => $applicationId),
            $bundle
        );

        $finalData = array();
        foreach ($data['Results'] as $result) {
            $finalData[] = $result;
            $lastElemntIndex = count($finalData) - 1;
            $finalData[$lastElemntIndex]['name'] = $finalData[$lastElemntIndex]['personTitle'] . ' ' .
                $finalData[$lastElemntIndex]['personFirstname'] . ' ' .
                $finalData[$lastElemntIndex]['personLastname'];
            unset($finalData[$lastElemntIndex]['personTitle']);
            unset($finalData[$lastElemntIndex]['personFirtName']);
            unset($finalData[$lastElemntIndex]['personLastName']);
        }

        return $finalData;
    }

    /**
     * Get the form table data - in this case simply invoke the same logic
     * as when rendered on a summary page, but provide the controller for context
     *
     * @return array
     */
    protected function getFormTableData($applicationId, $tableName)
    {
        return static::getSummaryTableData($applicationId, $this, $tableName);
    }

    /**
     * Add custom validation logic
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $post = (array)$this->getRequest()->getPost();
        if (!(array_key_exists('table', $post) && array_key_exists('action', $post['table']))) {
            $rows = $form->get('table')->get('rows')->getValue();
            $prevConvictionValidator =
                new \Common\Form\Elements\Validators\PreviousHistoryPenaltiesConvictionsPrevConvictionValidator();
            $prevConvictionValidator->setRows($rows);
            $prevConviction = $form->getInputFilter()->get('data')->get('prevConviction')->getValidatorChain();
            $prevConviction->attach($prevConvictionValidator);
        }
        return $form;
    }

    /**
     * Process action load data
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        $data = parent::processActionLoad($data);
        $returnData = ($this->getActionName() != 'add') ? array('data' => $data) : $data;

        return $returnData;
    }

    /**
     * Add offense
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit offense
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Process load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        $returnData['data'] = array(
            'id' => $data['id'],
            'version' => $data['version'],
        );
        if ($data['prevConviction'] === true) {
            $returnData['data']['prevConviction'] = 'Y';
        } elseif ($data['prevConviction'] === false) {
            $returnData['data']['prevConviction'] = 'N';
        } else {
            $returnData['data']['prevConviction'] = '';
        }
        $returnData['convictionsConfirmation'] = array('convictionsConfirmation' => $data['convictionsConfirmation']);
        return $returnData;
    }

    /**
     * Delete sub action
     *
     * @return Response
     */
    public function deleteAction()
    {
        return $this->delete();
    }
}

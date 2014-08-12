<?php

/**
 * ConvictionsPenalties Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

use Common\Form\Elements\Validators\PreviousHistoryPenaltiesConvictionsPrevConvictionValidator;

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
     * Holds the action service
     *
     * @var string
     */
    protected $actionService = 'Conviction';

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        'main' => array(
            'children' => array(
                'conviction' => array(
                    'mapFrom' => array(
                        'conviction'
                    )
                ),
                'person' => array(
                    'mapFrom' => array(
                        'person'
                    )
                )
            )
        )
    );

    /**
     * Hold the action data bundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'convictionDate',
            'notes',
            'court',
            'penalty'
        ),
        'children' => array(
            'person' => array(
                'properties' => array(
                    'id',
                    'version',
                    'title',
                    'forename',
                    'familyName'
                )
            )
        )
    );

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
     * Action save
     *
     * @todo Not sure whether we should be created a new person for each conviction, as these could be the same people
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $result = parent::actionSave($data['person'], 'Person');

        if ($this->getActionName() == 'add' && !empty($result)) {
            $data['conviction']['person'] = $result['id'];
        } else {
            $data['conviction']['person'] = $data['person']['id'];
        }

        $applicationId = $this->getIdentifier();
        $data['conviction']['application'] = $applicationId;

        parent::actionSave($data['conviction'], 'Conviction');
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
                'convictionDate',
                'notes',
                'court',
                'penalty'
            ),
            'children' => array(
                'person' => array(
                    'properties' => array(
                        'title',
                        'forename',
                        'familyName'
                    )
                )
            )
        );

        $data = $context->makeRestCall(
            'Conviction',
            'GET',
            array('application' => $applicationId),
            $bundle
        );

        $finalData = array();
        foreach ($data['Results'] as $result) {

            $row = $result;

            $row['name'] = $row['person']['title'] . ' ' . $row['person']['forename'] . ' ' . $row['person']['familyName'];

            unset($row['person']);

            $finalData[] = $row;
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
            $prevConvictionValidator = new PreviousHistoryPenaltiesConvictionsPrevConvictionValidator();
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

        if ($this->getActionName() == 'add') {
            return array();
        }

        $returnData = array(
            'person' => $data['person'],
            'conviction' => $data
        );

        unset($returnData['conviction']['person']);

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
        return array(
            'data' => array(
                'id' => $data['id'],
                'version' => $data['version'],
                'prevConviction' => $data['prevConviction']
            ),
            'convictionsConfirmation' => array(
                'convictionsConfirmation' => $data['convictionsConfirmation']
            )
        );
    }

    /**
     * Delete sub action
     *
     * @return Response
     */
    public function deleteAction()
    {
        // Need to delete the associated person
        $convictionId = $this->getActionId();

        $bundle = array(
            'properties' => null,
            'children' => array(
                'person' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $results = $this->makeRestCall('Conviction', 'GET', array('id' => $convictionId), $bundle);

        if (isset($results['person']['id'])) {
            $this->makeRestCall('Person', 'DELETE', array('id' => $results['person']['id']));
        }

        return $this->delete();
    }
}

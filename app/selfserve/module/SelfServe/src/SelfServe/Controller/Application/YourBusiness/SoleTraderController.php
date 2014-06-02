<?php

/**
 * Sole Trader Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * Sole Trader Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SoleTraderController extends YourBusinessController
{
    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data',
            ),
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
                'title',
                'firstName',
                'surname',
                'dateOfBirth',
                'otherNames',
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
     * Save data
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        $applicationId = $this->getIdentifier();
        $data['application'] = $applicationId;
        parent::save($data, 'Person');
    }

    /**
     * Get the form data
     *
     * @return array
     */
    protected function getFormData()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $bundle = array(
            'properties' => array(
                'id',
                'version',
                'title',
                'firstName',
                'surname',
                'dateOfBirth',
                'otherNames'
            ),
        );

        $data = $this->makeRestCall(
            'Person',
            'GET',
            array('application' => $applicationId),
            $bundle
        );

        $finalData = array();
        if (array_key_exists('Results', $data) !== false && count($data['Results']) >= 1) {
            $finalData['data']['title'] = $data['Results'][0]['title'];
            $finalData['data']['firstName'] = $data['Results'][0]['firstName'];
            $finalData['data']['surname'] = $data['Results'][0]['surname'];
            $finalData['data']['otherNames'] = $data['Results'][0]['otherNames'];
            $finalData['data']['dateOfBirth'] = $data['Results'][0]['dateOfBirth'];
            $finalData['data']['id'] = $data['Results'][0]['id'];
            $finalData['data']['version'] = $data['Results'][0]['version'];
        }

        return $finalData;
    }
}

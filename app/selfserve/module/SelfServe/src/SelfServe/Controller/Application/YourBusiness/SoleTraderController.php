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

    protected $service = 'Person';

    /**
     * Data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
                'id',
                'version',
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
     * Load data
     *
     * @param $id
     * @return array
     */
    protected function load($id)
    {
        $data = $this->makeRestCall(
            $this->getService(),
            'GET',
            array('application' => $id),
            $this->getDataBundle()
        );
        return array(
            'data' => count($data['Results']) ? $data['Results'][0] : array()
        );
    }
}

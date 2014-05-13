<?php

/**
 * OperatorType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

/**
 * OperatorType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatorTypeController extends TypeOfLicenceController
{
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
     */
    public function save($data)
    {
        $this->makeRestCall('Licence', 'PUT', $data['data']);

        return $this->goToNextStep();
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    public function load($id)
    {
        $bundle = array(
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'goodsOrPsv'
                    )
                )
            )
        );

        $application = $this->makeRestCall('Application', 'GET', array('id' => $id), $bundle);

        return array('data' => $application['licence']);
    }
}

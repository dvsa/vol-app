<?php

/**
 * OperatorLocation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

/**
 * OperatorLocation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatorLocationController extends TypeOfLicenceController
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
        if ($data['data']['niFlag'] == 1) {
            $data['data']['goodsOrPsv'] = 'goods';
        }

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
                        'niFlag'
                    )
                )
            )
        );

        $application = $this->makeRestCall('Application', 'GET', array('id' => $id), $bundle);

        return array('data' => $application['licence']);
    }
}

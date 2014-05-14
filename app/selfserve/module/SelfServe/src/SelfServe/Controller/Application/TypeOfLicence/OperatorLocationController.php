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
    protected function save($data)
    {
        if ($data['data']['niFlag'] == 1) {
            $data['data']['goodsOrPsv'] = 'goods';
        }

        return parent::save($data);
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    public function load($id)
    {
        return array('data' => $this->getLicenceData(array('niFlag')));
    }
}

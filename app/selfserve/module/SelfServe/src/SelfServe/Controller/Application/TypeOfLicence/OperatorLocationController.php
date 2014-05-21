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
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        if ($data['niFlag'] == 1) {
            $data['goodsOrPsv'] = 'goods';
        }

        return parent::save($data);
    }

    /**
     * Load data from id
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        return array('data' => $this->getLicenceData());
    }
}

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
     * Process the form
     *
     * @param array $data
     */
    public function processForm($data)
    {
        /*$operatorLocation = $data['data']['operator_location'];

        $licence = $this->getLicenceEntity();

        $data = array(
            'id' => $licence['id'],
            'niFlag' => (bool)$operatorLocation,
            'version' => $data['version'],
        );

        if ($operatorLocation == '1') {
            $data['goodsOrPsv'] = 'goods';
        }

        $this->processEdit($data, 'Licence');*/

        return $this->goToNextStep();
    }
}

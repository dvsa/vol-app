<?php

/**
 * Index Controller (Dashboard)
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Dashboard;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;

/**
 * Class IndexController
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexController extends AbstractActionController
{
    /**
     * Holds the organisation bundle
     *
     * @var array
     */
    private $organisationUserBundle = array(
        'properties' => array(

        ),
        'children' => array(
            'organisation' => array(
                'properties' => array('id')
            )
        )
    );

    /**
     * Method to add the required database entries and redirect to beginning
     * of the application journey.
     *
     * @return Response
     */
    public function createAction()
    {
        $user = $this->getUser();

        $data = [
            'version' => 1,
            'status' => 'lsts_new',
            'organisation' => $this->getOrganisationId($user['id']),
        ];

        $licenceResult = $this->makeRestCall('Licence', 'POST', $data);
        $licenceId = $licenceResult['id'];

        $data = [
            'licence' => $licenceId,
            'createdOn' => date('Y-m-d h:i:s'),
            'status' => 'apsts_not_submitted',
            'isVariation' => false
        ];

        $applicationResult = $this->makeRestCall('Application', 'POST', $data);
        $applicationId = $applicationResult['id'];

        $data = [
            'id' => $applicationId,
        ];

        $this->makeRestCall('ApplicationCompletion', 'POST', $data);

        return $this->redirectToRoute('Application', ['applicationId' => $applicationId]);
    }

    /**
     * Get organisation Id based on current user
     * @IMPORTANT User's can now be linked to more than 1 organisation, in the future there will be a dropdown for these
     * users to select which organisation they are dealing with, which will be stored in session so we will need @todo
     * some changes at that stage, for now we just grab the first organisation
     *
     * @throws \Exception
     * @return int
     */
    private function getOrganisationId($userId)
    {
        $organisation = $this->makeRestCall(
            'OrganisationUser',
            'GET',
            ['user' => $userId],
            $this->organisationUserBundle
        );

        if ($organisation['Count'] < 1) {
            throw new \Exception('Organisation not found');
        }

        return $organisation['Results'][0]['organisation']['id'];
    }

    /**
     * Currently there is no authentication mechanism, so userId is hardcoded
     *
     * @return array|Response
     */
    private function getUser()
    {
        return array('id' => 1);
    }
}

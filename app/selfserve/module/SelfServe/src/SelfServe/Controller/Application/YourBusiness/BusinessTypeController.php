<?php

/**
 * BusinessType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * BusinessType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeController extends YourBusinessController
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
     * Load data from id
     *
     * @param int $id
     */
    protected function load($id)
    {
        $organisationBundle = array(
            'children' => array(
                'type' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $data = array('data' => $this->getOrganisationData($organisationBundle));

        if (isset($data['data']['type']['id'])) {
            $data['data']['type'] = $data['data']['type']['id'];
        }

        return $data;
    }
}

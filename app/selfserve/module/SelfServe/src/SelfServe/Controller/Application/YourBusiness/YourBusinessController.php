<?php

/**
 * YourBusiness Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

use SelfServe\Controller\Application\ApplicationController;

/**
 * YourBusiness Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class YourBusinessController extends ApplicationController
{
    /**
     * Set the service for the "Free" save behaviour
     *
     * @var string
     */
    protected $service = 'Organisation';

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->goToFirstSubSection();
    }

    /**
     * Get organisation data
     *
     * @param array $properties
     * @return array
     */
    protected function getOrganisationData($properties = array())
    {
        if (is_array($properties)) {
            $properties = array_merge(
                array('id', 'version'),
                $properties
            );
        }

        $bundle = array(
            'children' => array(
                'licence' => array(
                    'children' => array(
                        'organisation' => array(
                            'properties' => $properties
                        )
                    )
                )
            )
        );

        $application = $this->makeRestCall('Application', 'GET', array('id' => $this->getIdentifier()), $bundle);

        return $application['licence']['organisation'];
    }
}

<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application;

use SelfServe\Controller\AbstractJourneyController;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractJourneyController
{
    /**
     * Redirect to the first section
     *
     * @return Resposne
     */
    public function indexAction()
    {
        return $this->goToFirstSection();
    }

    /**
     * Return an array of access keys
     *
     * @return array
     */
    protected function getAccessKeys()
    {
        if (empty($this->accessKeys)) {
            $licence = $this->getLicenceEntity();

            if (empty($licence)) {
                return array(null);
            }

            $type = str_replace(' ', '-', strtolower($licence['licenceType']));

            if (strstr($type, 'standard')) {
                $type = 'standard';
            }

            $this->accessKeys = array(
                ($licence['niFlag'] == 1 ? 'ni' : 'gb'),
                trim(strtolower($licence['goodsOrPsv']) . '-' . $type, '-')
            );
        }

        return $this->accessKeys;
    }

    /**
     * Get licence entity based on route id value
     *
     * @return array|object
     */
    protected function getLicenceEntity($applicationId = false)
    {
        if ( ! $applicationId ) {
            $applicationId = (int) $this->getIdentifier();
        }

        $bundle = array(
            'children' => array(
                'licence'
            )
        );

        $application = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);
        return $application['licence'];
    }
}

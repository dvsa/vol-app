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
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Application';

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
     * @param boolean $force
     * @return array
     */
    protected function getAccessKeys($force = false)
    {
        if (empty($this->accessKeys) || $force) {
            $licence = $this->getLicenceDataForAccess();

            if (empty($licence)) {
                return array(null);
            }

            $type = str_replace(' ', '-', strtolower($licence['licenceType']));

            if (strstr($type, 'standard')) {
                $type = 'standard';
            }

            $this->accessKeys = array(
                trim(strtolower($licence['goodsOrPsv']) . '-' . $type, '-')
            );

            if (isset($licence['niFlag']) && !is_null($licence['niFlag']) && $licence['niFlag'] !== '') {
                $this->accessKeys[] = ($licence['niFlag'] == 1 ? 'ni' : 'gb');
            }
        }

        return $this->accessKeys;
    }

    /**
     * Get licence entity based on route id value
     *
     * @return array|object
     */
    protected function getLicenceDataForAccess()
    {
        $bundle = array(
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'goodsOrPsv',
                        'niFlag',
                        'licenceType'
                    )
                )
            )
        );

        $application = $this->makeRestCall('Application', 'GET', array('id' => $this->getIdentifier()), $bundle);

        return $application['licence'];
    }
}

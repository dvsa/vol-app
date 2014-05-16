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
     * Cache licence data requests
     *
     * @var array
     */
    private $licenceData = array();

    /**
     * Check if is psv
     *
     * @var boolean
     */
    protected $isPsv;

    /**
     * Redirect to the first section
     *
     * @return Resposne
     */
    public function indexAction()
    {
        $completion = $this->getSectionCompletion();

        if (isset($completion['lastSection'])) {
            return $this->goToSection($completion['lastSection']);
        }

        return $this->goToFirstSection();
    }

    /**
     * Save the last section
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function preRender($view)
    {
        $this->saveLastSection();

        return parent::preRender($view);
    }

    /**
     * Save the last section
     */
    protected function saveLastSection()
    {
        // We use the full section completion as it gets cached and will be used again
        $completion = $this->getSectionCompletion();

        $data = array(
            'id' => $completion['id'],
            'version' => $completion['version'],
            'lastSection' => $this->getJourneyName() . '/' . $this->getSectionName() . '/' . $this->getSubSectionName()
        );

        $this->makeRestCall('ApplicationCompletion', 'PUT', $data);

        $completion['version']++;

        $this->setSectionCompletion($completion);
    }

    /**
     * Check if application is psv
     *
     * @return boolean
     */
    protected function isPsv()
    {
        if (is_null($this->isPsv)) {
            $data = $this->getLicenceData(array('goodsOrPsv'));

            if (strtolower($data['goodsOrPsv']) == 'psv') {
                $this->isPsv = true;
            } else {
                $this->isPsv = false;
            }
        }

        return $this->isPsv;
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

            if (strtolower($licence['goodsOrPsv']) == 'psv') {
                $this->isPsv = true;
            } else {
                $this->isPsv = false;
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
        return $this->getLicenceData(array('goodsOrPsv', 'niFlag', 'licenceType'));
    }

    /**
     * Get the licence data
     *
     * @param array $properties
     * @return array
     */
    protected function getLicenceData($properties = array())
    {
        $key = json_encode($properties);

        if (!isset($this->licenceData[$key])) {
            $bundle = array(
                'children' => array(
                    'licence' => array(
                        'properties' => $properties
                    )
                )
            );

            $application = $this->makeRestCall('Application', 'GET', array('id' => $this->getIdentifier()), $bundle);

            $this->licenceData[$key] = $application['licence'];
        }

        return $this->licenceData[$key];
    }
}

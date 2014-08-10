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
    const GOODS_OR_PSV_GOODS_VEHICLE = 'lcat_gv';
    const GOODS_OR_PSV_PSV = 'lcat_psv';

    const LICENCE_TYPE_RESTRICTED = 'ltyp_r';
    const LICENCE_TYPE_STANDARD_INTERNATIONAL = 'ltyp_si';
    const LICENCE_TYPE_STANDARD_NATIONAL = 'ltyp_sn';
    const LICENCE_TYPE_SPECIAL_RESTRICTED = 'ltyp_sr';

    /**
     * Holds the licenceDataBundle
     *
     * @var array
     */
    public static $licenceDataBundle = array(
        'children' => array(
            'licence' => array(
                'properties' => array(
                    'id',
                    'version',
                    'niFlag'
                ),
                'children' => array(
                    'goodsOrPsv' => array(
                        'properties' => array(
                            'id'
                        )
                    ),
                    'licenceType' => array(
                        'properties' => array(
                            'id'
                        )
                    ),
                    'organisation' => array(
                        'children' => array(
                            'type' => array(
                                'properties' => array(
                                    'id'
                                )
                            )
                        )
                    )
                )
            )
        )
    );

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
    protected $isPsv = null;

    /**
     * Licence type
     *
     * @var string
     */
    protected $licenceType = null;

    /**
     * Redirect to the first section
     *
     * @return Response
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
     * Check if the vehicle safety section is enabled
     *
     * @return boolean
     */
    public function isVehicleSafetyEnabled()
    {
        if (!$this->isPsv()) {
            return true;
        }

        return ($this->getSectionStatus('OperatingCentres') == 'complete');
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

        $foreignKey = $this->getJourneyConfig()['completionStatusJourneyIdColumn'];

        $data = array(
            'id' => $completion['id'],
            'version' => $completion['version'],
            'lastSection' => $this->getJourneyName() . '/' . $this->getSectionName() . '/' . $this->getSubSectionName()
        );

        $this->makeRestCall('ApplicationCompletion', 'PUT', $data);

        $completion['version'] ++;

        $this->setSectionCompletion($completion);
    }

    /**
     * Check if application is psv
     *
     * GetAccessKeys "should" always be called first so psv should be set
     *
     * @return boolean
     */
    protected function isPsv()
    {
        return $this->isPsv;
    }

    /**
     * Get the licence type
     *
     * @return string
     */
    protected function getLicenceType()
    {
        if (empty($this->licenceType)) {
            $licenceData = $this->getLicenceData();

            $this->licenceType = $licenceData['licenceType'];
        }

        return $this->licenceType;
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

            $this->accessKeys = array();

            $licence = $this->getLicenceData();

            if (empty($licence)) {
                return parent::getAccessKeys($force);
            }

            $goodsOrPsv = $this->getGoodsOrPsvFromLicenceData($licence);
            $type = $this->getLicenceTypeFromLicenceData($licence);

            if ($goodsOrPsv == 'psv') {
                $this->isPsv = true;
                $this->accessKeys[] = 'psv';
            } else {
                $this->isPsv = false;
                $this->accessKeys[] = 'goods';
            }

            $this->accessKeys[] = trim($goodsOrPsv . '-' . $type, '-');

            if (isset($licence['niFlag']) && !is_null($licence['niFlag']) && $licence['niFlag'] !== '') {
                $this->accessKeys[] = ($licence['niFlag'] == 1 ? 'ni' : 'gb');
            }

            $sectionCompletion = $this->getSectionCompletion();

            if (isset($sectionCompletion['sectionPaymentSubmissionStatus'])
                && $sectionCompletion['sectionPaymentSubmissionStatus'] == 2) {

                $this->accessKeys[] = 'paid';
            } else {
                $this->accessKeys[] = 'unpaid';
            }

            $this->accessKeys[] = $licence['organisation']['type']['id'];
        }

        return $this->accessKeys;
    }

    /**
     * Get Goods Or Psv From Licence Data
     *
     * @param array $licence
     * @return string|null
     */
    private function getGoodsOrPsvFromLicenceData($licence)
    {
        if (!isset($licence['goodsOrPsv']['id'])) {
            return null;
        }

        if ($licence['goodsOrPsv']['id'] == self::GOODS_OR_PSV_PSV) {
            return 'psv';
        }

        return 'goods';
    }

    /**
     * Get Licence Type From Licence Data
     *
     * @param array $licence
     * @return string|null
     */
    private function getLicenceTypeFromLicenceData($licence)
    {
        if (!isset($licence['licenceType']['id'])) {
            return null;
        }

        if (
            in_array(
                $licence['licenceType']['id'],
                [self::LICENCE_TYPE_STANDARD_INTERNATIONAL, self::LICENCE_TYPE_STANDARD_NATIONAL]
            )
        ) {
            return 'standard';
        }

        switch ($licence['licenceType']['id']) {
            case self::LICENCE_TYPE_STANDARD_INTERNATIONAL:
            case self::LICENCE_TYPE_STANDARD_NATIONAL:
                return 'standard';
            case self::LICENCE_TYPE_RESTRICTED:
                return 'restricted';
            case self::LICENCE_TYPE_SPECIAL_RESTRICTED:
                return 'special-restricted';
        }

        return null;
    }

    /**
     * Get the licence data
     *
     * @return array
     */
    protected function getLicenceData()
    {
        if (empty($this->licenceData)) {

            $application = $this->makeRestCall(
                'Application',
                'GET',
                array('id' => $this->getIdentifier()),
                self::$licenceDataBundle
            );

            $this->licenceData = $application['licence'];
        }

        return $this->licenceData;
    }

    /**
     * Upload a file
     *
     * @param array $file
     * @param array $data
     */
    protected function uploadFile($file, $data)
    {
        $uploader = $this->getUploader();
        $uploader->setFile($file);
        $uploader->upload();

        $file = $uploader->getFile();

        $fileData = $file->toArray();

        $licence = $this->getLicenceData();

        $fileData['fileName'] = $fileData['name'];
        $fileData['application'] = $this->getIdentifier();
        $fileData['licence'] = $licence['id'];

        unset($fileData['path']);
        unset($fileData['type']);
        unset($fileData['name']);

        $this->makeRestCall('Document', 'POST', array_merge($fileData, $data));
    }
}

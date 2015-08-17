<?php
/**
 * Goods Disc Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Admin\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Service to get / update disk to print
 *
 * Class GoodsDisc
 * @package Admin\Service
 */
class GoodsDisc extends AbstractData
{
    /**
     * Service name
     *
     * @var string
     */
    protected $serviceName = 'GoodsDisc';

    /**
     * Northern Ireland traffic area code
     */
    const NI_TRAFFIC_AREA_CODE = 'N';

    /**
     * Default page size for rest call
     */
    const PAGE_SIZE = 100;

    /**
     * Get Discs To Print Number
     * @todo remove
     * @param string $niFlag
     * @param string $operatorType
     * @param string $licenceType
     * @param string $discPrefix
     * @return int
     */
    public function getDiscsToPrint($niFlag = '', $operatorType = '', $licenceType = '', $discPrefix = '')
    {
        if (!$niFlag) {
            throw new \Exception('Error getting discs to print number - no operator location provided');
        }
        if (!$operatorType && $niFlag == 'N') {
            throw new \Exception('Error getting discs to print number - no operator type provided');
        }
        if (!$licenceType) {
            throw new \Exception('Error getting discs to print number - no licence type provided');
        }
        if (!$discPrefix) {
            throw new \Exception('Error getting discs to print number - no disc prefix provided');
        }
        $trafficArea = substr($discPrefix, 1, 1);
        if (!$trafficArea) {
            throw new \Exception('Error getting discs to print number - wrong traffic are code');
        }

        $bundle = $this->getBundle();
        $page = 1;
        $discsToPrint = [];
        do {
            // get pending discs
            $results = $this
                        ->getRestClient()
                        ->get(
                            '',
                            [
                            'bundle' => json_encode($bundle),
                            'limit' => self::PAGE_SIZE,
                            'page' => $page++,
                            'issuedDate' => 'NULL',
                            'ceasedDate' => 'NULL'
                            ]
                        );

            // filter discs using provided parameters
            if (is_array($results['Results']) && count($results['Results'])) {
                foreach ($results['Results'] as $result) {
                    if ($this->shouldFilterDisc($niFlag, $licenceType, $operatorType, $result)) {
                        $discsToPrint[] = $result;
                    }
                }
            }

        } while (count($results['Results']));
        return $discsToPrint;
    }

    /**
     * Should we filter disc
     * @todo remove
     * @param string $niFlag
     * @param string $licenceType
     * @param string $operatorType
     * @param array $disc
     * @return bool
     */
    protected function shouldFilterDisc($niFlag, $licenceType, $operatorType, $disc)
    {
        $entity = ($disc['isInterim'] === 'Y') ? 'application' : 'licence';
        // for NI licences we don't check operator type
        if ($niFlag == 'Y' && isset($disc['licenceVehicle']['licence']['trafficArea']['isNi']) &&
            !empty($disc['licenceVehicle']['licence']['trafficArea']['isNi']) &&
            isset($disc['licenceVehicle'][$entity]['licenceType']['id']) &&
            $disc['licenceVehicle'][$entity]['licenceType']['id'] == $licenceType &&
            isset($disc['licenceVehicle']['licence']['trafficArea']['id']) &&
            $disc['licenceVehicle']['licence']['trafficArea']['id'] == self::NI_TRAFFIC_AREA_CODE &&
            is_array($disc['licenceVehicle']['vehicle'])) {

            return true;

        }
        // for non-NI licences we should check operator type as well
        if ($niFlag == 'N' && isset($disc['licenceVehicle']['licence']['trafficArea']['isNi']) &&
            empty($disc['licenceVehicle']['licence']['trafficArea']['isNi']) &&
            isset($disc['licenceVehicle']['licence']['goodsOrPsv']['id']) &&
            $disc['licenceVehicle']['licence']['goodsOrPsv']['id'] == $operatorType &&
            isset($disc['licenceVehicle'][$entity]['licenceType']['id']) &&
            $disc['licenceVehicle'][$entity]['licenceType']['id'] == $licenceType &&
              // neet to pick up discs from all traffic areas apart from NI
            isset($disc['licenceVehicle']['licence']['trafficArea']['id']) &&
            $disc['licenceVehicle']['licence']['trafficArea']['id'] !== self::NI_TRAFFIC_AREA_CODE &&
            is_array($disc['licenceVehicle']['vehicle'])) {

            return true;

        }
        return false;
    }

    /**
     * Set is printing status on
     *
     * @param array $discs
     */
    public function setIsPrintingOn($discs = [])
    {
        $this->updateDiscs($discs, ['isPrinting' => 'Y']);
    }

    /**
     * Set is printing status off
     *
     * @param array $discs
     */
    public function setIsPrintingOff($discs = [])
    {
        $this->updateDiscs($discs, ['isPrinting' => 'N']);
    }

    /**
     * Set is printing status off, set issued date and number
     *
     * @param array $discs
     * @param int $startNumber
     */
    public function setIsPrintingOffAndAssignNumber($discs = [], $startNumber = null)
    {
        $this->updateDiscs($discs, ['isPrinting' => 'N', 'issuedDate' => strftime("%Y-%m-%d %H:%M:%S")], $startNumber);
    }

    /**
     * Update goods discs
     *
     * @param array $discs
     * @param array $data
     * @param int $number
     */
    private function updateDiscs($discs = [], $data = [], $number = null)
    {
        foreach ($discs as $disc) {
            $dataToUpdate = [
                'version' => $disc['version']
            ];
            if ($number) {
                $dataToUpdate['discNo'] = $number++;
            }
            $dataToUpdate = array_merge($data, $dataToUpdate);
            $this->getRestClient()->put('/' . $disc['id'], ['data' => json_encode($dataToUpdate)]);
        }
    }

    /**
     * Get bundle
     * @todo remove
     * @return array
     */
    public function getBundle()
    {
        $bundle = [
            'children' => [
                'licenceVehicle' => [
                    'children' => [
                        'licence' => [
                            'children' => [
                                'goodsOrPsv',
                                'licenceType',
                                'trafficArea'
                            ]
                        ],
                        'vehicle',
                        'application' => [
                            'children' => [
                                'licenceType'
                            ]
                        ]
                    ]
                ],
            ]
        ];
        return $bundle;
    }
}

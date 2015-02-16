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
     *
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
                    // for NI licences we don't check operator type
                    if ($niFlag == 'Y' && isset($result['licenceVehicle']['licence']['niFlag']) &&
                        $result['licenceVehicle']['licence']['niFlag'] == 'Y' &&
                        isset($result['licenceVehicle']['licence']['licenceType']['id']) &&
                        $result['licenceVehicle']['licence']['licenceType']['id'] == $licenceType &&
                        isset($result['licenceVehicle']['licence']['trafficArea']['id']) &&
                        $result['licenceVehicle']['licence']['trafficArea']['id'] == self::NI_TRAFFIC_AREA_CODE &&
                        is_array($result['licenceVehicle']['vehicle'])) {
                        $discsToPrint[] = $result;
                         // for non-NI licences we should check operator type as well
                    } elseif ($niFlag == 'N' && isset($result['licenceVehicle']['licence']['niFlag']) &&
                        $result['licenceVehicle']['licence']['niFlag'] == 'N' &&
                        isset($result['licenceVehicle']['licence']['goodsOrPsv']['id']) &&
                        $result['licenceVehicle']['licence']['goodsOrPsv']['id'] == $operatorType &&
                        isset($result['licenceVehicle']['licence']['licenceType']['id']) &&
                        $result['licenceVehicle']['licence']['licenceType']['id'] == $licenceType &&
                          // neet to pick up discs from all traffic areas apart from NI
                        isset($result['licenceVehicle']['licence']['trafficArea']['id']) &&
                        $result['licenceVehicle']['licence']['trafficArea']['id'] !== self::NI_TRAFFIC_AREA_CODE &&
                        is_array($result['licenceVehicle']['vehicle'])) {
                        $discsToPrint[] = $result;
                    }
                }
            }

        } while (count($results['Results']));
        return $discsToPrint;
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
     *
     * @return array
     */
    public function getBundle()
    {
        $bundle = [
            'properties' => ['id', 'version'],
            'children' => [
                'licenceVehicle' => [
                    'properties' => ['id'],
                    'children' => [
                        'licence' => [
                            'properties' => ['id', 'niFlag'],
                            'children' => [
                                'goodsOrPsv' => [
                                    'properties' => ['id']
                                ],
                                'licenceType' => [
                                    'properties' => ['id']
                                ],
                                'trafficArea' => [
                                    'properties' => ['id']
                                ],
                            ]
                        ],
                        'vehicle' => [
                            'properties' => [
                                'id',
                                'deletedDate'
                            ]
                        ]
                    ]
                ],
            ]
        ];
        return $bundle;
    }
}

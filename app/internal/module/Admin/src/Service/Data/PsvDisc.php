<?php
/**
 * PSV Disc Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Admin\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Entity\LicenceEntityService;

/**
 * Service to get / update disk to print
 *
 * Class PsvDisc
 * @package Admin\Service
 */
class PsvDisc extends AbstractData
{
    /**
     * Service name
     *
     * @var string
     */
    protected $serviceName = 'PsvDisc';

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
     * @param string $licenceType
     * @param string $discPrefix
     * @return int
     */
    public function getDiscsToPrint($licenceType = '', $discPrefix = '')
    {
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
                    if (isset($result['licence']['niFlag']) &&
                        ($result['licence']['niFlag'] == 'N') &&
                        isset($result['licence']['licenceType']['id']) &&
                        $result['licence']['licenceType']['id'] == $licenceType &&
                        isset($result['licence']['trafficArea']['id']) &&
                        $result['licence']['trafficArea']['id'] !== self::NI_TRAFFIC_AREA_CODE &&
                        isset($result['licence']['goodsOrPsv']['id']) &&
                        $result['licence']['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_PSV
                        ) {
                        $discsToPrint[] = $result;
                    }
                }
            }

        } while (count($results['Results']) === self::PAGE_SIZE);

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
     * Update psv discs
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
            ]
        ];
        return $bundle;
    }
}

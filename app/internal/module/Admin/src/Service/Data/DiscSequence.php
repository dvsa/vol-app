<?php
/**
 * Disc Sequence Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Admin\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

/**
 * Service to get disk prefixes list from DiscSequence
 *
 * Class DiscSequence
 * @package Admin\Service
 */
class DiscSequence extends AbstractData implements ListDataInterface
{
    /**
     * Licence type to prefixes mapping
     *
     * @var string
     */
    protected $serviceName = 'DiscSequence';

    /**
     * Licence type to prefixes mapping
     * 
     * @var array
     */
    protected $prefixes = [
        'ltyp_r'  => 'rPrefix', // Restricted
        'ltyp_sn' => 'snPrefix', // Standard National
        'ltyp_si' => 'siPrefix' // Standard International
    ];

    /**
     * Licence type to numbers mapping
     * 
     * @var array
     */
    protected $numbers = [
        'ltyp_r'  => 'restricted',
        'ltyp_sn' => 'standardNational',
        'ltyp_si' => 'standardInternational'
    ];

    /**
     * Northern Ireland traffic area code
     */
    const NI_TRAFFIC_AREA_CODE = 'N';

    /**
     * Default page size for rest call
     */
    const PAGE_SIZE = 20;

    /**
     * Fetch Disc Prefixes List Options
     *
     * @param mixed $context
     * @param bool $useGroups
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        if (!is_array($context)) {
            throw new \Exception('Error getting disc prefixes list - no parameters provided');
        }
        if (!isset($context['niFlag'])) {
            throw new \Exception('Error getting disc prefixes list - no operator location provided');
        }
        if ($context['niFlag'] == 'N' && !isset($context['goodsOrPsv'])) {
            throw new \Exception('Error getting disc prefixes list - no operator type provided');
        }
        if (!isset($context['licenceType'])) {
            throw new \Exception('Error getting disc prefixes list - no licence type provided');
        }
        $data = $this->fetchDiscPrefixesListData($context['niFlag'], $context['goodsOrPsv']);
        $ret = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                $ret[$item['id']] = $item[$this->prefixes[$context['licenceType']]];
            }
        }
        return $ret;
    }

    /**
     * Fetch Disc Prefixes List Data
     *
     * @param bool $niFlag
     * @param string $goodsOrPsv
     * @param array $bundle
     * @return array
     */
    public function fetchDiscPrefixesListData($niFlag = 'N', $goodsOrPsv = 'lcat_gv', $bundle = null)
    {
        if (is_null($this->getData('prefixes'))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $data = [];
            $page = 1;
            do {
                $result = $this
                            ->getRestClient()
                            ->get(
                                '',
                                [
                                    'bundle' => json_encode($bundle),
                                    'limit' => self::PAGE_SIZE,
                                    'page' => $page
                                ]
                            );
                $data = array_merge($data, $result['Results']);
                $page++;
            } while (count($result['Results']));
            $this->setData('prefixes', false);
            if (count($data)) {
                $finalResults = [];
                foreach ($data as $line) {
                    $includeRecord = true;
                    // don't need an empty record
                    if (!isset($line['trafficArea']['id']) ||
                        (!isset($line['goodsOrPsv']['id']) &&
                        $line['trafficArea']['id'] !== self::NI_TRAFFIC_AREA_CODE)) {
                        $includeRecord = false;
                    }
                    // filter by operating location
                    if ($niFlag == 'N' && $line['trafficArea']['id'] == self::NI_TRAFFIC_AREA_CODE) {
                        $includeRecord = false;
                    } elseif ($niFlag == 'Y' && $line['trafficArea']['id'] !== self::NI_TRAFFIC_AREA_CODE) {
                        $includeRecord = false;
                    }

                    // filter by operating type if this is not NI traffic area code
                    if ($line['trafficArea']['id'] !== self::NI_TRAFFIC_AREA_CODE &&
                        $goodsOrPsv !== $line['goodsOrPsv']['id']) {
                        $includeRecord = false;
                    }
                    if ($includeRecord) {
                        $finalResults[] = $line;
                    }
                }
                $this->setData('prefixes', $finalResults);
            }
        }
        return $this->getData('prefixes');
    }

    /**
     * Get bundle
     *
     * @return array
     */
    public function getBundle()
    {
        $bundle = array(
            'properties' => 'ALL',
            'children' => [
                'trafficArea' => [
                    'properties' => [
                        'id'
                    ]
                ],
                'goodsOrPsv' => [
                    'properties' => [
                        'id'
                    ]
                ]
            ]
        );
        return $bundle;
    }

    /**
     * Get disc number
     *
     * @param string $discSequence
     * @param string $licenceType
     * @return int
     */
    public function getDiscNumber($discSequence = '', $licenceType = '')
    {
        return $this->getDiscDetails('number', $discSequence, $licenceType);
    }

    /**
     * Get disc prefix
     *
     * @param string $discSequence
     * @param string $licenceType
     * @return string
     */
    public function getDiscPrefix($discSequence = '', $licenceType = '')
    {
        return $this->getDiscDetails('prefix', $discSequence, $licenceType);
    }

    /**
     * Get disc details
     *
     * @param string $type
     * @param int $discSequence
     * @param string $licenceType
     * @return mixed
     */
    protected function getDiscDetails($type = 'number', $discSequence = '', $licenceType = '')
    {
        if (!$discSequence || !$licenceType) {
            return false;
        }
        $value = ($type == 'number') ? $this->numbers[$licenceType] :  $this->prefixes[$licenceType];
        if (is_null($this->getData($type . '-' . $value))) {
            $bundle = [
                'properties' => [
                    $value
                ]
            ];

            $result = $this
                        ->getRestClient()
                        ->get(
                            '',
                            [
                                'bundle' => json_encode($bundle),
                                'id' => $discSequence
                            ]
                        );
            if (isset($result[$value])) {
                $this->setData($type . '-' . $value, $result[$value]);
            }
        }
        return $this->getData($type . '-' . $value);

    }

    /**
     * Set new start number
     *
     * @param string $licenceType
     * @param int $discSequence
     * @param int $startNumber
     */
    public function setNewStartNumber($licenceType = '', $discSequence = null, $startNumber = null)
    {
        if (!$licenceType) {
            throw new \Exception('Error setting start number - no licence type provided');
        }

        if (!$discSequence) {
            throw new \Exception('Error setting start number - no disc sequence provided');
        }

        if (!$startNumber) {
            throw new \Exception('Error setting start number - no start number provided');
        }

        $bundle = [
            'properties' => [
                'version'
            ]
        ];
        $details = $this->getRestClient()->get(['bundle' => json_encode($bundle), 'id' => $discSequence]);
        if (!isset($details['version'])) {
            throw new \Exception('Error setting start number - unable to get version');
        }

        $dataToUpdate = [
            $this->numbers[$licenceType] => $startNumber,
            'version' => $details['version']
        ];

        return $this->getRestClient()->put('/' . $discSequence, ['data' => json_encode($dataToUpdate)]);

    }
}

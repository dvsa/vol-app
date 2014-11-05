<?php
/**
 * Fee Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Service\Data;

use Common\Service\Fee\FeeGeneration;

/**
 * Service to get licence fees
 *
 * Class Fee
 * @package Olcs\Service
 */
class Fee extends FeeGeneration
{
    /**
     * Service name
     *
     * @var string
     */
    protected $serviceName = 'Fee';

    /**
     * Get fee data
     * 
     * @param array $params
     * @param array $bundle
     * @return array
     */
    public function getFees($params = [], $bundle = null)
    {
        $fees = $this->fetchFeesData($params, $bundle);
        return $fees;
    }


    /**
     * Fetch fee data
     * 
     * @param array $params
     * @param array $bundle
     * @return array
     */
    protected function fetchFeesData($params = [], $bundle = null)
    {
        if (is_null($this->getData('Fees'))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $params = array_merge($params, ['bundle' => json_encode($bundle)]);
            $results = $this->getRestClient()->get('', $params);
            $this->setData('Fees', $results);
        }

        return $this->getData('Fees');
    }

    /**
     * Get single fee by id
     * 
     * @param int $id
     * @return array
     */
    public function getFee($id = null)
    {
        if (is_null($this->getData('Fee' . $id))) {
            $bundle = $this->getBundle();
            $params = ['bundle' => json_encode($bundle), 'id' => $id];
            $result = $this->getRestClient()->get('', $params);
            $this->setData('Fee' . $id, $result);
        }
        return $this->getData('Fee' . $id);
    }

    /**
     * Update fee
     * 
     * @param array $params
     */
    public function updateFee($params = [])
    {
        $id = $params['id'];
        unset($params['id']);
        $this->getRestClient()->put('/' . $id, ['data' => json_encode($params)]);
    }

    /**
     * @return array
     */
    public function getBundle()
    {
        $bundle = [
            'properties' => [
                'id',
                'invoiceNo',
                'invoiceStatus',
                'description',
                'amount',
                'invoicedDate',
                'receiptNo',
                'receivedAmount',
                'receivedDate',
                'waiveReason',
                'version'
            ],
            'children' => [
                'feeStatus' => [
                    'properties' => [
                        'id',
                        'description'
                    ]
                ],
                'paymentMethod' => [
                    'properties' => [
                        'id',
                        'description'
                    ]
                ],
                'lastModifiedBy' => [
                    'properties' => [
                        'id',
                        'name'
                    ]
                ]
            ]
        ];
        return $bundle;
    }
}

<?php
/**
 * Fee Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Service to get licence fees
 *
 * Class Fee
 * @package Olcs\Service
 */
class Fee extends AbstractData
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
    public function getFees($params = array(), $bundle = null, $filters = array())
    {
        $fees = $this->fetchFeesData($params, $bundle);
        if (array_key_exists('feeStatus', $filters) !== false && count($filters['feeStatus'])) {
            $fees = $this->filterFeesDataByStatus($fees, $filters['feeStatus']);
        }
        return $fees;
    }


    /**
     * Fetch fee data
     * 
     * @param array $params
     * @param array $bundle
     * @return array
     */
    public function fetchFeesData($params = array(), $bundle = null)
    {
        if (is_null($this->getData('Fees'))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $params = array_merge($params, array('bundle' => json_encode($bundle)));
            $results = $this->getRestClient()->get('',$params);
            $this->setData('Fees', $results);
        }

        return $this->getData('Fees');
    }

    /**
     * Filter fee data
     * 
     * @param array $results
     * @return array
     */
    public function filterFeesDataByStatus($results, $statuses = array())
    {
        $fiteredResults = array(
            'Results' => array(),
            'Count' => 0
        );
        foreach ($results['Results'] as $result) {
            if (in_array($result['feeStatus']['id'], $statuses)) {
                $fiteredResults['Results'][] = $result;
                $fiteredResults['Count']++;
            }
        }
        return $fiteredResults;
    }

    /**
     * @return array
     */
    public function getBundle()
    {
        $bundle = array(
            'properties' => array(
                'invoiceNo',
                'invoiceStatus',
                'description',
                'amount',
                'invoicedDate',
                'receiptNo',
                'receivedDate',
            ),
            'children' => array(
                'feeStatus' => array(
                    'properties' => array(
                        'id',
                        'description'
                    )
                )
            )
        );
        return $bundle;
    }
    
}
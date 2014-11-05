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
    public function getFees($params = array(), $bundle = null)
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
    protected function fetchFeesData($params = array(), $bundle = null)
    {
        if (is_null($this->getData('Fees'))) {
            $bundle = is_null($bundle) ? $this->getBundle() : $bundle;
            $params = array_merge($params, array('bundle' => json_encode($bundle)));
            $results = $this->getRestClient()->get('', $params);
            $this->setData('Fees', $results);
        }

        return $this->getData('Fees');
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

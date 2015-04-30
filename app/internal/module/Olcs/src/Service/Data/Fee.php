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

            if (isset($params['bundle'])) {
                // if there are any bundle overrides in the params, merge
                // them in and then discard
                $bundle = array_replace_recursive($bundle, $params['bundle']);
                unset($params['bundle']);
            }

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
     * @return array
     */
    public function getBundle()
    {
        $bundle = [
            'children' => [
                'feeStatus' => [],
                'feeType' => [],
                'paymentMethod' => [],
                'lastModifiedBy' => []
            ]
        ];
        return $bundle;
    }
}

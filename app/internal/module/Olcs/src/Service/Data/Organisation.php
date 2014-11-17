<?php
/**
 * Organisation Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Organisation Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Organisation extends AbstractData
{
    /**
     * Service name
     *
     * @var string
     */
    protected $serviceName = 'Organisation';

    /**
     * Get single organisation by id
     * 
     * @param int $id
     * @param bool $cache
     * @return array
     */
    public function getOrganisation($id = null, $cache = true)
    {
        if (!$cache || is_null($this->getData('Organisation' . $id))) {
            $bundle = $this->getBundle();
            $params = ['bundle' => json_encode($bundle), 'id' => $id];
            $result = $this->getRestClient()->get('', $params);
            $this->setData('Organisation' . $id, $result);
        }
        return $this->getData('Organisation' . $id);
    }

    /**
     * Update organisation
     * 
     * @param array $params
     */
    public function updateOrganisation($params = [])
    {
        $id = $params['id'];
        unset($params['id']);
        $this->getRestClient()->update('/' . $id, ['data' => json_encode($params)]);
    }

    /**
     * Create organisation
     * 
     * @param array $params
     */
    public function createOrganisation($params = [])
    {
        return $this->getRestClient()->post(['data' => json_encode($params)]);
    }

    /**
     * Get bundle
     * 
     * @return array
     */
    public function getBundle()
    {
        $bundle = [
            'properties' => [
                'id',
                'name',
                'companyOrLLpNo',
                'version'
            ],
            'children' => [
                'type' => [
                    'properties' => [
                        'id',
                        'description'
                    ]
                ],
            ]
        ];
        return $bundle;
    }
}

<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as ListDto;

/**
 * Internal User data service
 *
 * @package Olcs\Service\Data
 */
class UserListInternal extends AbstractDataService implements ListDataInterface
{
    /**
     * @var string
     */
    protected $sort = 'p.forename';

    /**
     * @var string
     */
    protected $order = 'ASC';

    /**
     * @var int
     */
    protected $teamId = null;

    /**
     * Set teamnId
     *
     * @param int $teamId Team id
     *
     * @return $this
     */
    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * Get team id
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchUserListData();

        if (!is_array($data)) {
            return [];
        }

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * Fetch user list data
     *
     * @return array
     * @throw UnexpectedResponseException
     */
    public function fetchUserListData()
    {
        if (is_null($this->getData('userlist'))) {
            $params = [
                'sort' => $this->sort,
                'order' => $this->order
            ];
            $teamId = $this->getTeamId();

            if ((int) $teamId > 0) {
                $params['team'] = $teamId;
            }

            $dtoData = ListDto::create($params);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('userlist', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('userlist', $response->getResult()['results']);
            }
        }

        return $this->getData('userlist');
    }

    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $this->getPersonIdentifier($datum);
        }

        return $optionData;
    }

    /**
     * Returns a person row identifier for display as drop down value. This is either the person name, or if not
     * present fallback is the login ID
     *
     * @param array $datum Data
     *
     * @return string
     */
    private function getPersonIdentifier($datum)
    {
        if (isset($datum['contactDetails']['person'])) {
            return $datum['contactDetails']['person']['forename'] . ' ' .
            $datum['contactDetails']['person']['familyName'];
        } else {
            // use login ID
            return $datum['loginId'];
        }
    }

    /**
     * Format for groups
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function formatDataForGroups(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $parentId = $datum['team']['id'];

            if (!isset($optionData[$parentId])) {
                $optionData[$parentId] = [
                    'label' => $datum['team']['name'],
                    'options' => []
                ];
            }

            $optionData[$parentId]['options'][$datum['id']] = $this->getPersonIdentifier($datum);
        }

        return $optionData;
    }
}

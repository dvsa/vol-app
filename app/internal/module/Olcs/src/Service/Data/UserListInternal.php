<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Internal User data service
 *
 * @package Olcs\Service\Data
 */
class UserListInternal extends AbstractListDataService
{
    protected static $sort = 'p.forename';
    protected static $order = 'ASC';

    /**
     * @var int
     */
    protected $teamId = null;

    /**
     * @var bool
     */
    protected $excludeLimitedReadOnly = false;

    /**
     * @return bool
     */
    public function getExcludeLimitedReadOnly()
    {
        return $this->excludeLimitedReadOnly;
    }

    /**
     * @param bool $excludeLimitedReadOnly
     * @return void
     */
    public function setExcludeLimitedReadOnly($excludeLimitedReadOnly)
    {
        $this->excludeLimitedReadOnly = $excludeLimitedReadOnly;
    }

    /**
     * Set teamId
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
     * Fetch list data
     *
     * @param array $context Context
     *
     * @return array
     * @throws DataServiceException
     */
    public function fetchListData($context = null)
    {
        $data = $this->getData('userlist');

        if ($data !== null) {
            return $data;
        }

        $teamId = (int)$this->getTeamId();

        $response = $this->handleQuery(
            TransferQry\User\UserListInternal::create(
                [
                    'sort' => self::$sort,
                    'order' => self::$order,
                    'team' => ($teamId > 0 ? $teamId : null),
                    'excludeLimitedReadOnly' => $this->getExcludeLimitedReadOnly()
                ]
            )
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();

        $this->setData('userlist', ($result['results'] ?? null));

        return $this->getData('userlist');
    }

    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatData(array $data)
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
    protected function getPersonIdentifier(array $datum)
    {
        $label = null;
        if (isset($datum['contactDetails']['person'])) {
            $person = $datum['contactDetails']['person'];

            $label = trim($person['forename'] . ' ' . $person['familyName']);
        }

        if (!empty($label)) {
            return $label;
        }

        return $datum['loginId'];
    }

    /**
     * Format for groups
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatDataForGroups(array $data)
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

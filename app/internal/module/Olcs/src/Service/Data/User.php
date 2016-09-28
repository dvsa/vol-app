<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\User\UserList;

/**
 * User data service
 *
 * @package Olcs\Service\Data
 */
class User extends AbstractDataService implements ListDataInterface
{
    /**
     * @var string
     */
    protected $titleKey = 'loginId';

    /**
     * @var int
     */
    protected $team = null;

    /**
     * Set team
     *
     * @param int $team Team id
     *
     * @return $this
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return int
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the
     * data as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore
     * this flag if the data doesn't allow for option groups to be constructed.
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchUserListData($context);
        $ret = [];

        if (!is_array($data)) {
            return [];
        }

        foreach ($data as $datum) {
            $ret[$datum['id']] = $datum[$this->titleKey];
        }

        return $ret;
    }

    /**
     * Fetch user list data
     *
     * @param array $context Context
     *
     * @return array
     * @throw UnexpectedResponseException
     */
    public function fetchUserListData($context = [])
    {
        if (is_null($this->getData('userlist'))) {
            $params = [
                'sort' => 'loginId',
                'order' => 'ASC'
            ];
            $team   = $this->getTeam();

            if (!empty($team)) {
                $params['team'] = $team;
            }

            if (isset($context['isInternal'])) {
                $params['isInternal'] = $context['isInternal'];
            }

            $dtoData = UserList::create($params);
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
}

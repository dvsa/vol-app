<?php

/**
 * User data service
 *
 * @author someone <someone@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as ListDto;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * Internal User data service
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class UserListInternal extends AbstractDataService implements ListDataInterface
{
    protected $sort = 'p.forename';

    protected $order = 'ASC';

    /**
     * @var int
     */
    protected $team = null;

    /**
     * @param string $team
     * @return $this
     */
    public function setTeam($team)
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $context
     * @param bool $useGroups
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
     * @throws UnexpectedResponseException
     */
    public function fetchUserListData()
    {
        if (is_null($this->getData('userlist'))) {
            $params = [
                'sort' => $this->sort,
                'order' => $this->order
            ];
            $team = $this->getTeam();
            if (!empty($team)) {
                $params['team'] = $team;
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
     * @param array $data
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
     * @param $datum
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
     * @param array $data
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

<?php

namespace Olcs\Service\Data;

use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList;

/**
 * Presiding TC data service
 *
 * @package Olcs\Service\Data
 */
class PresidingTc extends User
{
    /**
     * @var string
     */
    protected $titleKey = 'name';

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
        if (is_null($this->getData('presiding-tc'))) {
            $params = [
                'sort' => 'name',
                'order' => 'ASC'
            ];

            $dtoData = GetList::create($params);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('presiding-tc', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('presiding-tc', $response->getResult()['results']);
            }
        }

        return $this->getData('presiding-tc');
    }
}

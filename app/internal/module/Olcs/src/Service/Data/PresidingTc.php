<?php

/**
 * Presiding TC data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Service\Data;

use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * Presiding TC data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
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
     * @return array
     */
    public function fetchUserListData()
    {
        if (is_null($this->getData('presiding-tc'))) {
            $params = [
                'sort' => 'name',
                'order' => 'ASC'
            ];

            $dtoData = GetList::create($params);
            $response = $this->handleQuery($dtoData);
            if ($response->isServerError() || $response->isClientError() || !$response->isOk()) {
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

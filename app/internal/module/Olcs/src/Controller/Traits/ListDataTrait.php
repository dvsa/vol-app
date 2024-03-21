<?php

namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query as TranferQry;

/**
 * Class ListDataTrait
 */
trait ListDataTrait
{
    /**
     * Get a list of doc templates
     *
     * @param int         $categoryId    Category to filter by
     * @param int         $subCategoryId Sub category to filter by
     * @param ?string $firstOption   First Option
     *
     * @return array
     */
    public function getListDataDocTemplates($categoryId = null, $subCategoryId = null, $firstOption = null)
    {
        $dto = \Dvsa\Olcs\Transfer\Query\DocTemplate\GetList::create(
            [
                'order' => 'ASC',
                'sort' => 'description',
                'category' => $categoryId,
                'subCategory' => $subCategoryId,
            ]
        );

        return $this->getListDataOptions($dto, 'id', 'description', $firstOption);
    }

    /**
     * Get a list of teams
     *
     * @param ?string $firstOption @see getListDataOptions
     *
     * @return array
     */
    public function getListDataTeam($firstOption = null)
    {
        $dto = \Dvsa\Olcs\Transfer\Query\Team\TeamListData::create(
            [
                'order' => 'ASC',
                'sort' => 'name',
            ]
        );

        return $this->getListDataOptions($dto, 'id', 'name', $firstOption);
    }

    /**
     * Get a list of users
     *
     * @param int    $teamId      Option team to filter by
     * @param ?string $firstOption @see getListDataOptions
     *
     * @return array of User login ID's
     */
    public function getListDataUser($teamId = null, $firstOption = null)
    {
        $params = [
            'order' => 'ASC',
            'sort' => 'loginId',
        ];

        if ((int)$teamId !== 0) {
            $params['team'] = $teamId;
        } else {
            $params['isInternal'] = true;
        }

        $dto = \Dvsa\Olcs\Transfer\Query\User\UserList::create($params);

        return $this->getListDataOptions($dto, 'id', 'loginId', $firstOption);
    }

    /**
     * Get a list of Enforcement areas for a traffic area
     *
     * @param string $trafficArea Traffic area
     * @param ?string $firstOption @see getListDataOptions
     *
     * @return array of enforcement areas eg ['21' => 'Nottingham', etc]
     */
    public function getListDataEnforcementArea($trafficArea, $firstOption = null)
    {
        $params = [
            'id' => $trafficArea,
        ];

        $dto = \Dvsa\Olcs\Transfer\Query\TrafficArea\Get::create($params);
        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleQuery($dto);

        if (!$response->isOk()) {
            // something went wrong, assume its a temporary error, as these list lookups should never fail
            return [];
        }

        $options = [];
        if (is_string($firstOption)) {
            $options[''] = $firstOption;
        }

        // iterate through to create an array of options
        foreach ($response->getResult()['trafficAreaEnforcementAreas'] as $item) {
            $key = $item['enforcementArea']['id'];
            $value = $item['enforcementArea']['name'];
            $options[$key] = $value;
        }

        return $options;
    }

    /**
     * Take a DTO and create an array of items suitable for a select element
     *
     * @param TranferQry\QueryInterface $dto         Dto
     * @param string                    $keyName     The key from the dto response to use as the select options id
     * @param string                    $valueName   The key from the dto response to use as the select options text
     * @param bool|string|array|null        $firstOption false = disable first option, or a string of the text for the first
     *                                               option
     *
     * @return array
     */
    private function getListDataOptions($dto, $keyName, $valueName, $firstOption = null)
    {
        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleQuery($dto);
        if (!$response->isOk()) {
            // something went wrong, assume its a temporary error, as these list lookups should never fail
            return [];
        }

        $options = [];
        // Do we need to add a default first option
        if (is_array($firstOption)) {
            $options = (array)$firstOption;
        }
        if (is_string($firstOption)) {
            $options[''] = $firstOption;
        }

        // iterate through to create an array of options
        foreach ($response->getResult()['results'] as $item) {
            $key = $item[$keyName];
            $value = $item[$valueName];
            $options[$key] = $value;
        }

        return $options;
    }
}

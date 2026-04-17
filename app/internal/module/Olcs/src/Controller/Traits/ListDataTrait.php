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
        $isFeatureEnabled = $this->isLettersDatabaseDrivenEnabled();

        $params = [
            'category' => $categoryId,
            'subCategory' => $subCategoryId,
            'order' => 'ASC',
            'sort' => 'description',
        ];

        $dto = \Dvsa\Olcs\Transfer\Query\DocTemplate\GetList::create($params);

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleQuery($dto);
        if (!$response->isOk()) {
            return [];
        }

        $options = [];
        // Add default first option
        if (is_array($firstOption)) {
            $options = (array)$firstOption;
        }
        if (is_string($firstOption)) {
            $options[''] = $firstOption;
        }

        // Build options array with [New] prefix for new templates
        foreach ($response->getResult()['results'] as $item) {
            $key = $item['id'];
            $value = $item['description'];

            // Add [New] prefix for templates with letterType when feature is enabled
            if ($isFeatureEnabled && !empty($item['letterType'])) {
                $value = '[New] ' . $value;
            }

            $options[$key] = $value;
        }

        // Sort array to put [New] templates first, then alphabetically within each group
        if ($isFeatureEnabled && count($options) > 1) {
            // Separate the first option (empty string key) if it exists
            $firstOption = null;
            if (isset($options[''])) {
                $firstOption = $options[''];
                unset($options['']);
            }

            // Sort remaining options
            uasort($options, function ($a, $b) {
                $aIsNew = str_starts_with($a, '[New]');
                $bIsNew = str_starts_with($b, '[New]');

                if ($aIsNew === $bIsNew) {
                    // Both new or both legacy - sort alphabetically (case-insensitive)
                    return strcasecmp($a, $b);
                }

                // [New] items first (return negative if $a is new and $b is not)
                return $bIsNew - $aIsNew;
            });

            // Re-add first option at the beginning if it existed
            if ($firstOption !== null) {
                $options = ['' => $firstOption] + $options;
            }
        }

        return $options;
    }

    /**
     * Check if letters database-driven feature is enabled
     *
     * @return bool
     */
    protected function isLettersDatabaseDrivenEnabled(): bool
    {
        $result = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled::create(
                ['ids' => [\Common\FeatureToggle::LETTERS_DATABASE_DRIVEN]]
            )
        );

        return $result->getResult()['isEnabled'] ?? false;
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

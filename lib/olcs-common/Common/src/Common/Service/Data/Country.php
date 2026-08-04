<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\ContactDetail\CountryList;

/**
 * Class Country
 *
 * @package Common\Service\Data
 */
class Country extends AbstractDataService implements ListData
{
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
            $optionData[$datum['id']] = $datum['countryDesc'];
        }

        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param string $category  Category
     * @param bool   $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function fetchListOptions($category, $useGroups = false)
    {
        $data = $this->fetchListData();

        if (!$data) {
            return [];
        }

        if (!empty($category)) {
            $data = $this->filterByCategory($data, $category);
        }

        return $this->formatData($data);
    }

    /**
     * Filter by category
     *
     * @param array $data Data
     * @param string $category Category
     *
     * @return array
     */
    public function filterByCategory($data, $category)
    {
        $filtered = [];

        $field = ('ecmtConstraint' === $category) ? 'constraints' : $category;

        foreach ($data as $state) {
            if (!isset($state[$field])) {
                continue;
            }
            if (!($category === 'isMemberState' && trim($state[$field]) === 'Y') && !($category !== 'isMemberState' && !empty($state[$field]))) {
                continue;
            }
            $filtered[] = $state;
        }

        return $filtered;
    }

    /**
     * Fetch list data
     *
     * @return array
     */
    public function fetchListData()
    {
        if (is_null($this->getData('Country'))) {
            $params = [
                'sort' => 'countryDesc',
                'order' => 'ASC'
            ];
            $dtoData = CountryList::create($params);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('Country', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('Country', $response->getResult()['results']);
            }
        }

        return $this->getData('Country');
    }
}

<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\LocalAuthority\LocalAuthorityList as LocalAuthorityQry;

/**
 * Class LocalAuthority
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LocalAuthority extends AbstractDataService implements ListData
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
            $optionData[$datum['id']] = $this->label($datum);
        }

        return $optionData;
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
            $taId = $datum['trafficArea']['id'];

            if (!isset($optionData[$taId])) {
                $optionData[$taId] = [
                    'label' => $datum['trafficArea']['name'],
                    'options' => []
                ];
            }

            $optionData[$taId]['options'][$datum['id']] = $this->label($datum);
        }

        return $optionData;
    }

    /**
     * An authority's option label. A replaced authority often keeps the public name of the one
     * that replaced it, so it is marked to tell the two apart where a list shows both.
     */
    private function label(array $datum): string
    {
        return empty($datum['isSuperseded']) ? $datum['description'] : $datum['description'] . ' (replaced)';
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    #[\Override]
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchListData();

        if (!$data) {
            return [];
        }

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * Ids of the local authorities another authority has replaced, which lists no longer offer
     *
     * @return int[]
     */
    public function fetchSupersededIds(): array
    {
        $data = $this->fetchListData() ?: [];

        return array_values(array_map(
            static fn (array $authority) => (int) $authority['id'],
            array_filter($data, static fn (array $authority) => !empty($authority['isSuperseded']))
        ));
    }

    /**
     * Fetch list data
     *
     * @return array
     * @throw DataServiceException
     */
    public function fetchListData()
    {
        /*
         * we had a restriction here, to fetch no more then 1000 records
         * now it's removed as discussed with P.F., R.C, A.P.
         */
        if (is_null($this->getData('LocalAuthority'))) {
            $dtoData = LocalAuthorityQry::create([]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $data = $response->getResult()['results'];
            $this->setData('LocalAuthority', $data);
        }

        return $this->getData('LocalAuthority');
    }
}

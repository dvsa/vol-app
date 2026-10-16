<?php

namespace Dvsa\Olcs\Db\Service\Search;

use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SysParamRepo;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SysParamEntity;
use Dvsa\Olcs\Db\Exceptions\SearchDateFilterParseException;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Db\Service\Search\Indices\AbstractIndex;
use OpenSearch\Client;

/**
 * Class Search
 *
 * @package Olcs\Db\Service\Search
 */
class Search implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const MAX_NUMBER_OF_RESULTS = 10000;

    protected array $filters = [];

    protected array $filterTypes = [];

    /**
     * @var array
     */
    protected $dateRanges = [];

    /**
     * @var string
     */
    protected $sort = '';

    /**
     * @var string
     */
    protected $order = '';

    public function __construct(
        protected Client $client,
        AuthorizationService $authService,
        protected SysParamRepo $sysParamRepo,
    ) {
        $this->authService = $authService;
    }

    /**
     * Get the OpenSearch client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get sort
     *
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set sort
     *
     * @param string $sort Sort
     *
     * @return void
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * Get order
     *
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param string $order Order
     *
     * @return void
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Submit a search request to OpenSearch
     *
     * @param string $query   The string you are searching for
     * @param array  $indexes The indexes to search, this is now only used to idenitify which query template to use
     * @param int    $page    Starting page, for pagination
     * @param int    $limit   Number of results to return
     *
     * @return array
     */
    public function search($query, $indexes = [], $page = 1, $limit = 10)
    {
        $queryTemplate = $this->getQueryTemplate($indexes);
        if ($queryTemplate === false) {
            throw new \RuntimeException('Cannot generate an elasticsearch query, is the template missing');
        }

        $searchTypes = array_filter(
            array_map(
                $this->getSearchType(...),
                $indexes,
            ),
            fn($item) => $item !== null,
        );

        $queryBody = new QueryTemplate(
            $queryTemplate,
            $query,
            $this->getFilters(),
            $this->getFilterTypes(),
            $this->getDateRanges(),
            $searchTypes,
        );

        if (!empty($this->getSort()) && !empty($this->getOrder())) {
            $queryBody->setSort([$this->getSort() => strtolower($this->getOrder())]);
        }

        if (!$this->isAnonymousUser() && $this->isInternalUser() && $indexes[0] !== 'irfo') {
            $exemptTeams = str_getcsv((string) $this->sysParamRepo->fetchValue(SysParamEntity::DATA_SEPARATION_TEAMS_EXEMPT), ',', '"', '\\');
            if (!in_array($this->getCurrentUser()->getTeam()->getId(), $exemptTeams)) {
                $queryBody->setPostFilter($this->getInternalUserTAPostFilter($indexes[0]));
            }
        }

        $queryBody->setSize($limit);
        $queryBody->setFrom($limit * ($page - 1));

        /**
         * This deals with asking OpenSearch for the filters / aggregation terms we want.
         */
        foreach ($this->getFilterNames() as $filterName) {
            $queryBody->addAggregation(
                $filterName,
                [
                    'terms' => [
                        'field' => $filterName,
                        'order' => ['_key' => 'asc'],
                        'size' => 25,
                    ],
                ]
            );
        }

        // Search on the given indices only, otherwise search is executed against all indices
        $resultSet = $this->getClient()->search([
            'index' => implode(',', $indexes),
            'body' => $queryBody->toArray(),
        ]);

        $totalHits = (int) ($resultSet['hits']['total']['value'] ?? 0);

        $response = [];

        // Limit max number of results to prevent the "Result window is too large" error
        $response['Count'] = min($totalHits, self::MAX_NUMBER_OF_RESULTS);

        $response['Results'] = $this->processResults($resultSet['hits']['hits'] ?? []);

        $response['Filters'] = $this->processFilters($resultSet['aggregations'] ?? []);

        return $response;
    }

    /**
     * Get the query template if it exists
     *
     * @param array $indexes Indexes, used to identify which query template to use
     *
     * @return string|bool Path and file of the template, or false if doesn't exist
     */
    private function getQueryTemplate($indexes)
    {
        if ($this->isAnonymousUser() || $this->isExternalUser()) {
            $file = __DIR__ . '/templates/selfserve/' . $indexes[0] . '.json';
        } else {
            $file = __DIR__ . '/templates/' . $indexes[0] . '.json';
        }

        if (file_exists($file)) {
            return $file;
        }

        return false;
    }

    /**
     * Process results
     *
     * @param array $hits The hits.hits element of the search response
     *
     * @return array
     */
    protected function processResults(array $hits)
    {
        $f = new UnderscoreToCamelCase();

        $response = [];

        foreach ($hits as $hit) {
            $raw = $hit['_source'] ?? [];
            $refined = [];
            foreach ($raw as $key => $value) {
                $refined[lcfirst((string) $f->filter($key))] = $value;
            }

            $response[] = $refined;
        }

        return $response;
    }

    /**
     * Process filters
     *
     * @param array $aggregations Aggregations
     *
     * @return array
     */
    protected function processFilters(array $aggregations)
    {
        $return = [];

        $f = new UnderscoreToCamelCase();

        foreach ($aggregations as $aggregation => $value) {
            $return[lcfirst((string) $f->filter($aggregation))] = $value['buckets'];
        }

        return $return;
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function getFilterTypes(): array
    {
        return $this->filterTypes;
    }

    public function setFilters(array $filters, array $filterTypes = []): self
    {
        $f = new CamelCaseToUnderscore();

        foreach ($filters as $filterName => $value) {
            $this->filters[strtolower((string) $f->filter($filterName))] = $value;
            $this->filterTypes[strtolower((string) $f->filter($filterName))] = $filterTypes[$filterName] ?? QueryTemplate::FILTER_TYPE_DYNAMIC;
        }

        return $this;
    }

    /**
     * Returns an array of filter names (array keys from the $this->requiredFilters array)
     *
     * @return array
     */
    public function getFilterNames()
    {
        return array_keys($this->getFilters());
    }

    /**
     * Get date ranges
     *
     * @return array
     */
    public function getDateRanges()
    {
        return $this->dateRanges;
    }

    /**
     * Sets the filters.
     * Requires the dates in three parts ['year','month','day'] or [0-9]{4}\-[0-9]{2}\-[0-9]{2} string
     *
     * @param array $dateRanges Date ranges
     *
     * @return array
     */
    public function setDateRanges(array $dateRanges)
    {
        $f = new CamelCaseToUnderscore();

        foreach ($dateRanges as $filterName => $value) {
            if (is_array($value)) {
                $value = (!empty($value['year']) && !empty($value['month']) && !empty($value['day']))
                    ? sprintf('%04d-%02d-%02d', $value['year'], $value['month'], $value['day'])
                    : null;
                if (!is_null($value) && strtotime($value) === false) {
                    $exception = new SearchDateFilterParseException('invalid date filter');
                    $exception->setDateField($filterName);
                    throw $exception;
                }
            } elseif (is_string($value) && preg_match('/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/', $value)) {
                // value already matches the format required
            } else {
                $value = null;
            }

            if (!empty($value)) {
                $this->dateRanges[strtolower((string) $f->filter($filterName))] = $value;
            }
        }
        return $this;
    }

    /**
     * Update the section 26 attribute in the vehicle indexes
     *
     * @param array $ids            Array of vehicle.id
     * @param bool  $section26Value Set or unset the value
     *
     * @return boolean If success
     * @throws \RuntimeException If any document in the bulk update failed
     */
    public function updateVehicleSection26(array $ids, $section26Value)
    {
        // No IDs, therefore nothing to do (an empty bool/should would otherwise match every document)
        if ($ids === []) {
            return true;
        }

        // Build a query to search where vehicle id is one of the IDs
        $should = [];
        foreach ($ids as $id) {
            $should[] = ['match' => ['veh_id' => $id]];
        }

        // Search both vehicle indexes, with size set to a large value
        $resultSet = $this->getClient()->search([
            'index' => 'vehicle_current,vehicle_removed',
            'body' => [
                'query' => ['bool' => ['should' => $should]],
                'size' => 1000,
            ],
        ]);

        $hits = $resultSet['hits']['hits'] ?? [];

        // No results found, therefore nothing to do
        if ($hits === []) {
            return true;
        }

        // Create a bulk request to update all the section 26 values
        $body = [];
        foreach ($hits as $hit) {
            $body[] = ['update' => ['_index' => $hit['_index'], '_id' => $hit['_id']]];
            $body[] = ['doc' => ['section_26' => $section26Value ? 1 : 0]];
        }

        $bulkResponse = $this->getClient()->bulk(['body' => $body]);

        if (!empty($bulkResponse['errors'])) {
            $failed = array_filter(
                $bulkResponse['items'] ?? [],
                fn(array $item) => isset($item['update']['error'])
            );

            throw new \RuntimeException(
                'Section 26 bulk update failed for ' . count($failed) . ' document(s): ' . json_encode(array_values($failed))
            );
        }

        return true;
    }

    /**
     * @return array A bool query restricting internal users to their own GB/NI side of the data
     */
    protected function getInternalUserTAPostFilter($searchIndex)
    {
        $isNi = in_array($this->getCurrentUser()->getTeam()->getTrafficArea()->getId(), TrafficArea::NI_TA_IDS);
        $disallowedTrafficAreaIds = $isNi ? TrafficArea::GB_TA_IDS : TrafficArea::NI_TA_IDS;

        $postFilter = ['bool' => ['must_not' => []]];
        foreach ($disallowedTrafficAreaIds as $taId) {
            $postFilter['bool']['must_not'][] = ['match' => ['ta_id' => $taId]];
        }

        if ($searchIndex === 'application') {
            $postFilter['bool']['must'][] = ['match' => ['ni_flag' => $isNi]];
        }

        return $postFilter;
    }

    protected function getSearchType(string $index): ?AbstractIndex
    {
        $index = ucwords($index);
        $class = '\\Olcs\\Db\\Service\\Search\\Indices\\' . $index;

        if (!class_exists($class)) {
            return null;
        }

        return new $class();
    }
}

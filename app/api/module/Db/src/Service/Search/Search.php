<?php

namespace Dvsa\Olcs\Db\Service\Search;

use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SysParamRepo;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SysParamEntity;
use Elastica\Aggregation\Terms;
use Elastica\Client;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use Elastica\Query;
use Elastica\ResultSet;
use Dvsa\Olcs\Db\Exceptions\SearchDateFilterParseException;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Db\Service\Search\Indices\AbstractIndex;

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
     * Get the Elastic client
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
     * Submit a search request to elastic
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

        $elasticaQuery = new QueryTemplate(
            $queryTemplate,
            $query,
            $this->getFilters(),
            $this->getFilterTypes(),
            $this->getDateRanges(),
            $searchTypes,
        );

        if (!empty($this->getSort()) && !empty($this->getOrder())) {
            $elasticaQuery->setSort([$this->getSort() => strtolower($this->getOrder())]);
        }

        if (!$this->isAnonymousUser() && $this->isInternalUser() && $indexes[0] !== 'irfo') {
            $exemptTeams = str_getcsv((string) $this->sysParamRepo->fetchValue(SysParamEntity::DATA_SEPARATION_TEAMS_EXEMPT));
            if (!in_array($this->getCurrentUser()->getTeam()->getId(), $exemptTeams)) {
                $elasticaQuery->setPostFilter($this->getInternalUserTAPostFilter($indexes[0]));
            }
        }

        $elasticaQuery->setSize($limit);
        $elasticaQuery->setFrom($limit * ($page - 1));

        /**
         * This deals with asking elastic for the filters / aggregation terms we want.
         */
        $filterNames = $this->getFilterNames();
        if (isset($filterNames)) {
            foreach ($filterNames as $filterName) {
                $terms = new Terms($filterName);
                $terms->setField($filterName);
                $terms->setOrder('_term', 'ASC');
                $terms->setSize(25);

                $elasticaQuery->addAggregation($terms);
            }
        }

        //Search on the index.
        $es = new \Elastica\Search($this->getClient());

        // Add indices, otherwise search is executed against all indices
        $es->addIndices($indexes);

        $response = [];
        $resultSet = $es->search($elasticaQuery);

        // Limit max number of results to prevent the ES "Result window is too large" error
        $response['Count'] = ($resultSet->getTotalHits() > self::MAX_NUMBER_OF_RESULTS)
            ? self::MAX_NUMBER_OF_RESULTS
            : $resultSet->getTotalHits();

        $response['Results'] = $this->processResults($resultSet);

        $response['Filters'] = $this->processFilters($resultSet->getAggregations());

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
     * @param ResultSet $resultSet Result set
     *
     * @return array
     */
    protected function processResults(ResultSet $resultSet)
    {
        $f = new UnderscoreToCamelCase();

        $response = [];

        foreach ($resultSet as $result) {
            /** @var \Elastica\Result $result */
            $raw = $result->getSource();
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
     */
    public function updateVehicleSection26(array $ids, $section26Value)
    {
        // Build a query to search where vehicle id is one of the IDs
        $queryBool = new Query\BoolQuery();
        foreach ($ids as $id) {
            $match = new MatchQuery();
            $match->setField('veh_id', $id);
            $queryBool->addShould($match);
        }

        $query = new Query();
        $query->setQuery($queryBool);
        // set size to a large value
        $query->setSize(1000);

        // Search both vehicle indexes
        $search = new \Elastica\Search($this->getClient());
        $search->addIndex('vehicle_current');
        $search->addIndex('vehicle_removed');
        $resultSet = $search->search($query);

        // No results found, therefore nothing to do
        if ($resultSet->count() === 0) {
            return true;
        }

        // Create a bulk request to update all the section 26 values
        $bulk = new \Elastica\Bulk($this->getClient());
        foreach ($resultSet->getResults() as $result) {
            /* @var $result \Elastica\Result */

            $action = new \Elastica\Bulk\Action(\Elastica\Bulk\Action::OP_TYPE_UPDATE);
            $action->setId($result->getId());
            $action->setIndex($result->getIndex());
            $action->setSource(['doc' => ['section_26' => $section26Value ? 1 : 0]]);
            $bulk->addAction($action);
        }

        return $bulk->send()->isOk();
    }

    /**
     * @return BoolQuery
     */
    protected function getInternalUserTAPostFilter($searchIndex)
    {
        $postFilter = new BoolQuery();
        $isNi = in_array($this->getCurrentUser()->getTeam()->getTrafficArea()->getId(), TrafficArea::NI_TA_IDS);
        $disallowedTrafficAreaIds = $isNi ? TrafficArea::GB_TA_IDS : TrafficArea::NI_TA_IDS;
        foreach ($disallowedTrafficAreaIds as $taId) {
            $postFilter->addMustNot(new MatchQuery('ta_id', $taId));
        }

        if ($searchIndex === 'application') {
            $postFilter->addMust(new MatchQuery('ni_flag', $isNi));
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

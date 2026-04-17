<?php

namespace Dvsa\Olcs\Db\Service\Search;

use DomainException;
use Elastica\Query;
use InvalidArgumentException;
use Dvsa\Olcs\Db\Service\Search\Indices\AbstractIndex;
use Dvsa\Olcs\Db\Service\Search\Indices\Terms\ComplexTermInterface;
use RuntimeException;

/**
 * Class QueryTemplate
 *
 * @package Olcs\Db\Service\Search
 */
class QueryTemplate extends Query
{
    public const FILTER_TYPE_DYNAMIC = 'DYNAMIC';
    public const FILTER_TYPE_FIXED = 'FIXED';
    public const FILTER_TYPE_COMPLEX = 'COMPLEX';
    public const FILTER_TYPE_BOOLEAN = 'BOOLEAN';

    /**
     * @param AbstractIndex[] $searchTypes
     */
    public function __construct(
        string $filename,
        string $searchTerm,
        array $filters = [],
        array $filterTypes = [],
        array $dateRanges = [],
        protected array $searchTypes = [],
    ) {
        if (!file_exists($filename)) {
            throw new RuntimeException("Query template file '" . $filename . "' is missing");
        }

        $searchTermReplace = json_encode($searchTerm);

        if ($searchTermReplace === false) {
            throw new RuntimeException(
                "Search term '" . $searchTerm . "' gives invalid json. Error: " . json_last_error_msg()
            );
        }

        // OLCS-14386 - don't use trim to remove " from the encoded $searchTerm
        $template = str_replace(
            '%SEARCH_TERM%',
            substr(substr($searchTermReplace, 1), 0, -1),
            file_get_contents($filename)
        );

        $this->_params = json_decode($template, true);

        if (empty($this->_params)) {
            throw new RuntimeException(
                "Empty params for query template file '" . $filename . "' and search term '" . $searchTerm . "'"
            );
        }

        // apply filters
        $this->applyFilters($filters, $filterTypes);

        // apply date ranges
        $this->applyDateRanges($dateRanges);
    }

    private function applyFilters(array $filters, array $filterTypes): self
    {
        if (empty($filters)) {
            return $this;
        }

        foreach ($filters as $field => $value) {
            if (empty($field) || $value === '') {
                continue;
            }

            switch ($filterTypes[$field]) {
                case self::FILTER_TYPE_COMPLEX:
                    foreach ($this->searchTypes as $searchType) {
                        $filters = $searchType->getFilters();

                        foreach ($filters as $filter) {
                            $filter->applySearch($this->_params['query']['bool']);
                        }
                    }
                    break;

                case self::FILTER_TYPE_FIXED:
                    $fields = explode('|', (string) $field);
                    foreach ($fields as $subField) {
                        $this->_params['query']['bool']['must']['bool']['must']['bool']['should'][] = [
                            'terms' => [
                                $subField => explode('|', (string) $value),
                            ],
                        ];
                    }
                    break;
                case self::FILTER_TYPE_DYNAMIC:
                    $this->_params['query']['bool']['filter'][] = [
                        'term' => [
                            $field => $value,
                        ],
                    ];
                    break;
                case self::FILTER_TYPE_BOOLEAN:
                    if ((int)$value === 1) {
                        $this->_params['query']['bool']['must']['bool']['must'][] = [
                            'exists' => [
                                'field' => $field,
                            ],
                        ];
                    } else {
                        $this->_params['query']['bool']['must_not'][] = [
                            'exists' => [
                                'field' => $field,
                            ],
                        ];
                    }
                    break;
                default:
                    throw new DomainException('Invalid filter type: ' . $filterTypes[$field]);
            }
        }

        return $this;
    }

    /**
     * Apply date ranges
     *
     * @param array $dates Dates
     *
     * @return $this
     */
    private function applyDateRanges($dates)
    {
        if (empty($dates)) {
            return $this;
        }

        foreach ($dates as $fieldName => $value) {
            $lcFieldName = strtolower((string) $fieldName);

            if (str_ends_with($lcFieldName, 'from_and_to')) {
                /* from_and_to allows a single date field to be used as a terms filter whilst keeping the
                 * individual Day/Month/Year input fields. The 'from_and_to' is identified and a single date is
                 * added as a query match rather than a range (for efficiency)
                 */
                $fieldName = substr((string) $fieldName, 0, -12);

                $this->_params['query']['bool']['filter'][] = [
                    'term' => [
                        $fieldName => $value
                    ]
                ];
            } elseif (str_ends_with($lcFieldName, 'from')) {
                $criteria = [];

                $fieldName = substr((string) $fieldName, 0, -5);
                $criteria['from'] = $value;

                // Let's now look for the to field.
                $toFieldName = $fieldName . '_to';

                if (!empty($dates[$toFieldName])) {
                    $criteria['to'] = $dates[$toFieldName];
                }

                $this->_params['query']['bool']['filter'][] = [
                    'range' => [
                        $fieldName => $criteria
                    ]
                ];
            }
        }

        return $this;
    }
}

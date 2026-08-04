<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Data\Object\Search\Aggregations\DateRange;
use Common\Module;
use Common\Service\Table\Formatter\SearchPeopleName;
use Common\Service\Table\Formatter\SearchPeopleRecord;

/**
 * Class People
 * @package Common\Data\Object\Search
 */
class People extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'People';

    /**
     * @var string
     */
    protected $key = 'people';

    /**
     * @var string
     */
    protected $searchIndices = 'person';

    /**
     * Contains an array of the instantiated Date Ranges classes.
     *
     * @var array
     */
    protected $dateRanges = [];

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Returns an array of filters for this index
     *
     * @return array
     */
    #[\Override]
    public function getFilters()
    {
        if (empty($this->filters)) {
            $this->filters = [
                new Filter\FoundType(),
                new Filter\TransportManagerLicenceStatus(),
                new Filter\FoundBy(),
                new Filter\LicenceStatus(),
                new Filter\MergedStatus(),
            ];
        }

        return $this->filters;
    }

    /**
     * Returns an array of date ranges for this index
     *
     * @return array
     */
    #[\Override]
    public function getDateRanges()
    {
        if ($this->dateRanges === []) {
            $this->dateRanges = [
                new DateRange\DateOfBirthFromAndTo()
            ];
        }

        return $this->dateRanges;
    }

    #[\Override]
    public function getVariables()
    {
        return [
            'title' => $this->getTitle(),
            'empty_message' => 'search-no-results-internal',
            'action_route' => [
                'route' => 'create_transport_manager',
                'params' => ['action' => null]
            ]
        ];
    }

    /**
     * Get settings
     *
     * @return array
     */
    #[\Override]
    public function getSettings()
    {
        return [
            'crud' => [
                'actions' => [
                    'add' => [
                        'label' => 'Create Transport Manager',
                        'class' => 'govuk-button',
                        'requireRows' => false
                    ],
                ],
            ],
            'paginate' => [
                'limit' => [
                    'options' => [10, 25, 50]
                ]
            ]
        ];
    }

    /**
     * Get columns
     *
     * @return array
     */
    #[\Override]
    public function getColumns()
    {
        return [
            ['title' => 'Found as', 'name' => 'foundAs'],
            [
                'title' => 'Record',
                'formatter' => SearchPeopleRecord::class
            ],
            [
                'title' => 'Name',
                'formatter' => SearchPeopleName::class
            ],
            [
                'title' => 'DOB',
                'name' => 'personBirthDate',
                'formatter' => static fn($row) => empty($row['personBirthDate']) ?
                    'Not known' : date(Module::$dateFormat, strtotime($row['personBirthDate']))
            ],
            [
                'title' => 'Date added',
                'name' => 'dateAdded',
                'formatter' => static fn($row) => empty($row['dateAdded']) ? 'NA' : date(Module::$dateFormat, strtotime($row['dateAdded']))
            ],
            [
                'title' => 'Date removed',
                'name' => 'dateRemoved',
                'formatter' => static fn($row) => empty($row['dateRemoved']) ? 'NA' : date(Module::$dateFormat, strtotime($row['dateRemoved']))
            ],
            [
                'title' => 'Disq?',
                'name' => 'disqualified',
                'formatter' => static function ($row) {
                    return $row['disqualified'];
                }
            ]
        ];
    }
}

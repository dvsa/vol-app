<?php

namespace Olcs\Data\Object\Search;

use Olcs\Data\Object\Search\Aggregations\Terms as Filter;
use Olcs\Data\Object\Search\Aggregations\DateRange as DateRange;

/**
 * Class Publication
 * @package Olcs\Data\Object\Search
 */
class Publication extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Publication';

    /**
     * @var string
     */
    protected $key = 'publication';

    /**
     * @var string
     */
    protected $searchIndices = 'publication';

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
     * Returns an array of date ranges for this index
     *
     * @return array
     */
    public function getDateRanges()
    {
        if (empty($this->dateRanges)) {

            $this->dateRanges = [
                new DateRange\PublishedDateFrom(),
                new DateRange\PublishedDateTo(),
            ];
        }

        return $this->dateRanges;
    }

    /**
     * Returns an array of filters for this index
     *
     * @return array
     */
    public function getFilters()
    {
        if (empty($this->filters)) {

            $this->filters = [
                new Filter\TrafficArea(),
                new Filter\PublicationType(),
                new Filter\DocumentStatus(),
                new Filter\PublicationSection()
            ];
        }

        return $this->filters;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            'paginate' => [
                'limit' => [
                    'default' => 25,
                    'options' => array(10, 25, 50)
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'title' => 'Traffic area',
                'name' => 'taName'
            ),
            array(
                'title' => 'Pub type',
                'name' => 'pubType'

            ),
            array(
                'title' => 'Doc status',
                'type' => 'RefData',
                'name' => 'description'
            ),
            array(
                'title' => 'Close date',
                'formatter' => 'Date',
                'name' => 'pubDate'
            ),
            array(
                'title' => 'Publication section',
                'name' => 'pubSecDesc'
            ),
            array(
                'title' => 'Publication details',
                'name' => 'text1'
            )
        );
    }
}

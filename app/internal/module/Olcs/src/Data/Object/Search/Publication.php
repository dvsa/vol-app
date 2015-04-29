<?php

namespace Olcs\Data\Object\Search;

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
    public function getFilters()
    {
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
                'title' => 'Closed date',
                'formatter' => 'Date',
                'name' => 'pubDate'
            ),
            array(
                'title' => 'Publication details',
                'name' => 'pubSecDesc'
            )
        );
    }
}

<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\SearchIrfoOrganisationOperatorNo;

/**
 * Class IrfoOrganisation
 * @package Common\Data\Object\Search
 */
class IrfoOrganisation extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'IRFO';

    /**
     * @var string
     */
    protected $key = 'irfo';

    /**
     * @var string
     */
    protected $searchIndices = 'irfo';

    /**
     * Contains an array of the instantiated filters classes.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @return array
     */
    #[\Override]
    public function getSettings()
    {
        return [
            'paginate' => [
                'limit' => [
                    'options' => [10, 25, 50]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    #[\Override]
    public function getColumns()
    {
        return [
            [
                'title' => 'Operator no',
                'formatter' => SearchIrfoOrganisationOperatorNo::class
            ],
            ['title' => 'Operator name', 'name' => 'orgName'],
        ];
    }
}

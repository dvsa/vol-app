<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\SearchAddressOperatorName;

/**
 * Class PsvDisc
 * @package Common\Data\Object\Search
 */
class PsvDisc extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Psv Disc';

    /**
     * @var string
     */
    protected $key = 'psv_disc';

    /**
     * @var string
     */
    protected $searchIndices = 'psv_disc';

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
        if ($this->filters === []) {
            $this->filters = [
                new Filter\LicenceStatus(),
            ];
        }

        return $this->filters;
    }

    /**
     * @return array
     */
    #[\Override]
    public function getColumns()
    {
        return [
            [
                'title' => 'Licence number',
                'name' => 'licNo',
                'formatter' => static fn($data) => '<a class="govuk-link" href="/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>'
            ],
            ['title' => 'Licence status', 'name' => 'licStatusDesc'],
            [
                'title' => 'Operator name',
                'name' => 'orgName',
                'formatter' => SearchAddressOperatorName::class
            ],
            ['title' => 'Disc Number', 'name' => 'discNo'],
        ];
    }
}

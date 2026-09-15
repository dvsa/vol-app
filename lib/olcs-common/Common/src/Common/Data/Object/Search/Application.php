<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\SearchAddressOperatorName;
use Common\Service\Table\Formatter\SearchApplicationLicenceNo;

/**
 * Class Licence
 * @package Common\Data\Object\Search
 */
class Application extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Applications';

    /**
     * @var string
     */
    protected $key = 'application';

    /**
     * @var string
     */
    protected $searchIndices = 'application';

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
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
                new Filter\ApplicationStatus(),
                new Filter\GoodsOrPsv(),
            ];
        }

        return $this->filters;
    }

    /**
     * get columns
     *
     * @return array
     */
    #[\Override]
    public function getColumns()
    {
        return [
            [
                'title' => 'Application id',
                'name' => 'appId',
                'formatter' => static fn($data) => '<a class="govuk-link" href="/application/' . $data['appId'] . '">' . $data['appId'] . '</a>'
            ],
            ['title' => 'Application status', 'name' => 'appStatusDesc'],
            [
                'title' => 'Date received',
                'formatter' => Date::class,
                'name' => 'receivedDate'
            ],
            [
                'title' => 'Licence number',
                'name' => 'licNo',
                'formatter' => SearchApplicationLicenceNo::class
            ],
            ['title' => 'Licence status', 'name' => 'licStatusDesc'],
            ['title' => 'Licence type', 'name' => 'licTypeDesc'],
            [
                'title' => 'Operator name',
                'name' => 'orgName',
                'formatter' => SearchAddressOperatorName::class
            ],
            ['title' => 'Authorisation vehicles', 'name' => 'totAuthVehicles'],
            ['title' => 'Authorisation trailers', 'name' => 'totAuthTrailers'],
        ];
    }
}

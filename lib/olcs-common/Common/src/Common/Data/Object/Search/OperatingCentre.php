<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;

/**
 * Class Address
 * @package Common\Data\Object\Search
 */
class OperatingCentre extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Operating Centres';

    /**
     * @var string
     */
    protected $key = 'address';

    /**
     * @var string
     */
    protected $searchIndices = 'address';

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
                new Filter\EntityType(),
                new Filter\LicenceType(),
                new Filter\LicenceStatus(),
                new Filter\TrafficArea(),
                new Filter\ApplicationStatus(),
                //new Filter\OppositionStatus(),
                //new Filter\Opposition(),
                //new Filter\Complaints(),
            ];
        }

        return $this->filters;
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
            [
                'title' => 'Licence number',
                'name' => 'licNo',
                'formatter' => static fn($data) => '<a class="govuk-link" href="/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>/'
                . '<br />' . $data['licStatusDesc']
            ],
            [
                'title' => 'Operator name',
                'name' => 'orgName',
                'formatter' => static fn($data) => '<a class="govuk-link" href="/operator/' . $data['orgId'] . '">' . $data['orgName'] . '</a>'
            ],
            [
                'title' => 'Address',
                'formatter' => static function ($row) {
                    $address = [

                        $row['street'],
                        $row['locality'],
                        '<br />' . $row['town'],
                        $row['postcode']
                    ];
                    return implode(', ', $address);
                }
            ],
        ];
    }
}

<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms as Filter;
use Common\Service\Table\Formatter\SearchCasesCaseId;
use Common\Service\Table\Formatter\SearchCasesName;

/**
 * Class Licence
 * @package Common\Data\Object\Search
 */
class Cases extends InternalSearchAbstract
{
    /**
     * @var string
     */
    protected $title = 'Case';

    /**
     * @var string
     */
    protected $key = 'case';

    /**
     * @var string
     */
    protected $searchIndices = 'case';

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
                new Filter\LicenceStatus(),
                new Filter\ApplicationStatus(),
                new Filter\CaseType(),
                new Filter\CaseStatus(),
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
            ['title' => 'Case type', 'name' => 'caseTypeDesc'],
            [
                'title' => 'Case Id',
                'name' => 'caseId',
                'formatter' => SearchCasesCaseId::class
            ],
            ['title' => 'Case type', 'name' => 'caseStatusDesc'],
            [
                'title' => 'Licence number',
                'name' => 'licNo',
                'formatter' => static fn($data) => '<a class="govuk-link" href="/licence/' . $data['licId'] . '">' . $data['licNo'] . '</a>'
            ],
            ['title' => 'Licence status', 'name' => 'licStatusDesc'],
            [
                'title' => 'Application Id',
                'name' => 'appId',
                'formatter' => static function ($data) {
                    if (!empty($data['appId'])) {
                        return '<a class="govuk-link" href="/application/' . $data['appId'] . '">'
                        . $data['appId']
                        . '</a>';
                    }
                    return 'N/a';
                }
            ],
            [
                'title' => 'Application Status',
                'name' => 'appStatusDesc',
                'formatter' => static function ($data) {
                    if (!empty($data['appStatusDesc'])) {
                        return $data['appStatusDesc'];
                    }
                    return 'N/a';
                }
            ],
            [
                'title' => 'Name',
                'formatter' => SearchCasesName::class
            ],
            ['title' => 'Case status', 'name' => 'caseStatusDesc'],
        ];
    }
}

<?php

namespace Olcs\Data\Object\Search;

/**
 * Class Licence
 * @package Olcs\Data\Object\Search
 */
class Cases extends SearchAbstract
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
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Case type', 'name'=> 'caseTypeDesc'],
            ['title' => 'Case Id', 'name'=> 'caseId'],
            ['title' => 'Licence number', 'name'=> 'licNo'],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            ['title' => 'Application Id', 'name'=> 'appId'],
            ['title' => 'Application Status', 'name'=> 'appStatusDesc'],
            ['title' => 'Operator name', 'name'=> 'orgName'],
            ['title' => 'Case status', 'name'=> 'caseStatusDesc'],
        ];
    }
}

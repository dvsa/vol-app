<?php

namespace Olcs\Data\Object\Search;

/**
 * Class PsvDisc
 * @package Olcs\Data\Object\Search
 */
class PsvDisc extends SearchAbstract
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
     * @return array
     */
    public function getColumns()
    {
        return [
            ['title' => 'Licence number', 'name'=> 'licNo'],
            ['title' => 'Licence status', 'name'=> 'licStatusDesc'],
            ['title' => 'Operator name', 'name'=> 'orgName'],
            ['title' => 'VRM', 'name'=> ''],
            ['title' => 'Disc Number', 'name'=> 'discNo'],
            ['title' => 'Specified date', 'name'=> ''],
            ['title' => 'Removed date', 'name'=> ''],
        ];
    }
}

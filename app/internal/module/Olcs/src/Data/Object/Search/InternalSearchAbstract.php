<?php

namespace Olcs\Data\Object\Search;

use Common\Data\Object\Search\SearchAbstract as CommonSearchAbstract;

/**
 * Class InternalSearchAbstract
 * @package Olcs\Data\Object\Search
 */
abstract class InternalSearchAbstract extends CommonSearchAbstract
{
    protected $displayGroup = 'internal-search';
}

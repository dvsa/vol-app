<?php

namespace Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\Terms\TermsAbstract;
use Common\Data\Object\Search\SearchAbstract as CommonSearchAbstract;
use InvalidArgumentException;

/**
 * Class InternalSearchAbstract
 * @package Common\Data\Object\Search
 */
abstract class InternalSearchAbstract extends CommonSearchAbstract
{
    protected $displayGroup = 'internal-search';

    /** @throws InvalidArgumentException */
    public function getFilter(string $name): TermsAbstract
    {
        $name = preg_replace_callback('/(^|_)([a-z])/', fn($m) => strtoupper($m[2]), $name);
        foreach ($this->getFilters() as $filter) {
            if (str_ends_with($filter::class, $name)) {
                return $filter;
            }
        }

        throw new InvalidArgumentException(sprintf('Filter named %s not found', $name));
    }
}

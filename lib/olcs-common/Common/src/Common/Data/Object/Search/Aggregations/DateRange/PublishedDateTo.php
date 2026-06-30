<?php

namespace Common\Data\Object\Search\Aggregations\DateRange;

/**
 * Date Range class.
 *
 * @package Common\Data\Object\Search\DateRange
 * @author Valtech <uk@valtech.co.uk>
 */
class PublishedDateTo extends DateRangeAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'search.form.filter.publication-date-to';

    /**
     * The actual name of the field to ask for filter information for. SUFFIX with "To"
     *
     * @var string
     */
    protected $key = 'pubDateTo';
}

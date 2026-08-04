<?php

namespace Common\Data\Object\Search\Aggregations\DateRange;

/**
 * Date Range class.
 *
 * @package Common\Data\Object\Search\DateRange
 * @author Valtech <uk@valtech.co.uk>
 */
class PublishedDateFrom extends DateRangeAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'search.form.filter.publication-date-from';

    /**
     * The actual name of the field to ask for filter information for. SUFFIX with "From"
     *
     * @var string
     */
    protected $key = 'pubDateFrom';
}

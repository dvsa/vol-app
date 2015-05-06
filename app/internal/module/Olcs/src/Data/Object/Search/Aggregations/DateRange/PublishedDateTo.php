<?php
namespace Olcs\Data\Object\Search\Aggregations\DateRange;

/**
 * Date Range class.
 *
 * @package Olcs\Data\Object\Search\DateRange
 * @author Valtech <uk@valtech.co.uk>
 */
class PublishedDateTo extends DateRangeAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Publish date to';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'pubDate';
}

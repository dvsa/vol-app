<?php
namespace Olcs\Data\Object\Search\DateRange;

/**
 * Date Range class.
 *
 * @package Olcs\Data\Object\Search\DateRange
 * @author Valtech <uk@valtech.co.uk>
 */
class PublishedDateFrom extends DateRangeAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Application status';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'appStatusDesc';
}
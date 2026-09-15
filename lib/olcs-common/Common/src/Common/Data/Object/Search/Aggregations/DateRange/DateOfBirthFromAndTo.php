<?php

namespace Common\Data\Object\Search\Aggregations\DateRange;

/**
 * Birth Date range class. Used as a date filter rather than a range.
 *
 * @package Common\Data\Object\Search\DateRange
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class DateOfBirthFromAndTo extends DateRangeAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Date of birth';

    /**
     * The actual name of the field to ask for filter information for. SUFFIX with "From"
     *
     * @var string
     */
    protected $key = 'personBirthDateFromAndTo';
}

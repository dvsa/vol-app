<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * FoundBy filter class.
 *
 * @package Common\Data\Object\Search\Filter
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class FoundBy extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Found as';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'foundAs';
}

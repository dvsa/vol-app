<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * AddressOpposition
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class AddressOpposition extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Opposition';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'opposition';
}

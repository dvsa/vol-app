<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * AddressComplaint
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class AddressComplaint extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Complaint';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'complaint';
}

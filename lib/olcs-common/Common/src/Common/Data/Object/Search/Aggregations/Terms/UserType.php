<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * UserType filter class.
 *
 * @package Common\Data\Object\Search\Filter
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UserType extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Type';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'userType';
}

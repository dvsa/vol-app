<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * Address condition and undertaking
 *
 * @package Common\Data\Object\Search\Filter
 */
class AddressConditionUndertaking extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'C/U';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'conditions';
}

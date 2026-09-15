<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * FoundType filter class.
 *
 * @package Common\Data\Object\Search\Filter
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class FoundType extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Found type';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'foundType';
}

<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * Localauthority filter class.
 *
 * @package Common\Data\Object\Search\Filter
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LocalAuthority extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Local Authority';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'laName';
}

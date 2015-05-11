<?php
namespace Olcs\Data\Object\Search\Aggregations\Terms;

/**
 * Licence Type filter class.
 *
 * @package Olcs\Data\Object\Search\Filter
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class LicenceType extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Licence type';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'licTypeDesc';
}

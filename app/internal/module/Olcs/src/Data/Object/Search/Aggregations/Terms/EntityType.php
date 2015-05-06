<?php
namespace Olcs\Data\Object\Search\Aggregations\Terms;

/**
 * Trading Name filter class.
 *
 * @package Olcs\Data\Object\Search\Filter
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class EntityType extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Entity type';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'orgTypeDesc';
}

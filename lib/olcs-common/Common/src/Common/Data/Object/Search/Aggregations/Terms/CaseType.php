<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * Case Type filter class.
 *
 * @package Common\Data\Object\Search\Filter
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CaseType extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'Case type';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'caseTypeDesc';
}

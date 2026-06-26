<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * Publication Section from filter class.
 *
 * @package Common\Data\Object\Search\Filter
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class PublicationSection extends TermsAbstract
{
    /**
     * The human readable title of this filter. This may also be used in the front-end (not sure yet).
     *
     * @var string
     */
    protected $title = 'search.form.filter.publication-section';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'pubSecDesc';
}

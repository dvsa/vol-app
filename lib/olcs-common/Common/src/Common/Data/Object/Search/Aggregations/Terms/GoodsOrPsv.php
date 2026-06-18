<?php

namespace Common\Data\Object\Search\Aggregations\Terms;

/**
 * Goods or PSV filter class.
 *
 * @package Common\Data\Object\Search\Filter
 */
class GoodsOrPsv extends TermsAbstract
{
    /**
     * The human readable title of this filter.
     *
     * @var string
     */
    protected $title = 'search.form.filter.goods-or-psv';

    /**
     * The actual name of the field to ask for filter information for.
     *
     * @var string
     */
    protected $key = 'goodsOrPsvDesc';
}

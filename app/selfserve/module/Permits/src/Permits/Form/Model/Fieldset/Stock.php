<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Stock")
 */
class Stock
{
    /**
     * @Form\Name("stock")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"data-module":"radios"}
     * })
     * @Form\Options({
     *      "label_attributes":{"class": "govuk-label govuk-radios__label govuk-label--s"},
     *      "input_class": "Common\Form\Input\StockInput"
     * })
     * @Form\Type("DynamicRadio")
     */
    public $stock;
}

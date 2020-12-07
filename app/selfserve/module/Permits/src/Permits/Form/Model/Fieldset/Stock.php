<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Stock")
 */
class Stock
{
    /**
     * @Form\Name("stock")
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"data-module":"radios"}
     * })
     * @Form\Options({
     *      "input_class": "Common\Form\Input\StockInput"
     * })
     * @Form\Type("DynamicRadio")
     */
    public $stock;
}

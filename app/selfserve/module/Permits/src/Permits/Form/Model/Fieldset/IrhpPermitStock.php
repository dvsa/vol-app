<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("IrhpPermitStock")
 */
class IrhpPermitStock
{
    /**
     * @Form\Name("irhpPermitStock")
     * @Form\Attributes({
     *     "id": "stock",
     *     "radios_wrapper_attributes": {"data-module":"radios"}
     * })
     * @Form\Options({
     *     "input_class": "Common\Form\Input\StockInput"
     * })
     * @Form\Type("DynamicRadio")
     */
    public $irhpPermitStock;

    /**
     * @Form\Name("previousIrhpPermitStock")
     * @Form\Type("Hidden")
     */
    public $previousIrhpPermitStock = null;
}

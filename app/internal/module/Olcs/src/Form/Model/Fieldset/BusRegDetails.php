<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class BusRegDetails extends Base
{
    /**
     * @Form\Type("Hidden")
     * @Form\Attributes({
     *      "id":"fields[isTxcApp]"
     * })
     */
    public $isTxcApp = null;

    /**
     * @Form\Type("Hidden")
     * @Form\Attributes({
     *      "id":"fields[isLatestVariation]"
     * })
     */
    public $isLatestVariation = null;

    /**
     * @Form\Type("Hidden")
     * @Form\Attributes({
     *      "id":"fields[status]"
     * })
     */
    public $status = null;
}

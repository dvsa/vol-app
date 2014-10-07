<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class EbsrPackUploadFields
{
    /**
     * @Form\Options({"label": "Choose File"})
     * @Form\Type("File")
     * This input class first runs the configured field filters and validators and then runs the EBSR validators,
     * @Form\Input("Olcs\InputFilter\EbsrFileInput")
     * @Form\Filter({"name": "DecompressToTmp"})
     */
    public $file;
}

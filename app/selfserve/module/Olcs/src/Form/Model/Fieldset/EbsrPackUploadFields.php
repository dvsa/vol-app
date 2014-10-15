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
     * @Form\Input("Zend\InputFilter\FileInput")
     * @Form\Filter({"name": "DecompressUploadToTmp"})
     * @Form\Validator({"name": "FileMimeType", "options":{"mimeType": "application/zip"}})
     */
    public $file;
}

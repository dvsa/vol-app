<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("reportUploadActions")
 */
class ReportUploadActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large", "id": "upload"})
     * @Form\Options({
     *     "label": "Upload"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $upload = null;
}

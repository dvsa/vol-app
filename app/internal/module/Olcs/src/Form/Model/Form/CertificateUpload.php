<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("certificate-upload")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class CertificateUpload
{
    /**
     * @Form\Attributes({"id":"file", "class": "file-upload"})
     * @Form\Options({
     *     "label":
     * "internal.transport-manager.competences.form.upload.header",
     *     "hint":
     * "internal.transport-manager.competences.form.upload.text"
     * })
     * @Form\Type("\Common\Form\Elements\Types\MultipleFileUpload")
     */
    public $file = null;
}

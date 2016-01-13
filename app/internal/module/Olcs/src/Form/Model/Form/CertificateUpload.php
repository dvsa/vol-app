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
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Options({
     *     "label":
     * "transport-manager.competences.form.upload.header",
     *     "hint":
     * "transport-manager.competences.form.upload.text"
     * })
     */
    public $file = null;
}

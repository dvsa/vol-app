<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-previous-licences-details")
 */
class TmPreviousLicencesDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"long","id":"lic-no"})
     * @Form\Options({"label":"internal.transport-manager.previous-licences.form.lic-no"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $licNo = null;

    /**
     * @Form\Attributes({"class":"long","id":"holderName"})
     * @Form\Options({"label":"internal.transport-manager.previous-licences.form.holder-name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $holderName = null;
}

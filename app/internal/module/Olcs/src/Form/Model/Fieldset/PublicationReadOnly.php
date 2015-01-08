<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("readOnly")
 */
class PublicationReadOnly
{
    /**
     * @Form\Options({"label":"Publication No."})
     */
    public $publicationNo = null;

    /**
     * @Form\Options({"label":"Status"})
     */
    public $status = null;

    /**
     * @Form\Options({"label":"Publication date"})
     */
    public $publicationDate = null;

    /**
     * @Form\Options({"label":"Type/Area"})
     */
    public $typeArea = null;

    /**
     * @Form\Options({"label":"Section"})
     */
    public $section = null;

    /**
     * @Form\Options({"label":"Traffic area"})
     */
    public $trafficArea = null;
}

<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-licence-history")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class LicenceHistory
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Name("questionsHint")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Messages")
     */
    public $questionsHint;

    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LicenceHistoryData")
     * @Form\Options({"label": "application_previous-history_licence-history_Data"})
     */
    public $data;

    /**
     * @Form\Name("eu")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LicenceHistoryEu")
     * @Form\Options({"label": "application_previous-history_licence-history_EU"})
     */
    public $eu;

    /**
     * @Form\Name("pi")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LicenceHistoryPi")
     * @Form\Options({"label": "application_previous-history_licence-history_PI"})
     */
    public $pi;

    /**
     * @Form\Name("assets")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LicenceHistoryAssets")
     * @Form\Options({"label": "application_previous-history_licence-history_assets"})
     */
    public $assets;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}

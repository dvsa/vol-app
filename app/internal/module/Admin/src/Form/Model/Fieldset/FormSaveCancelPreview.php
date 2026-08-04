<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class FormSaveCancelPreview
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "aria-label": "Save and Continue",
     *     "id": "save"
     * })
     * @Form\Options({"label": "Save"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"govuk-button govuk-button--secondary",
     *     "id": "cancel"
     * })
     * @Form\Options({
     *     "label": "Cancel",
     *     "keepForReadOnly": true,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;

    /**
     * @Form\Attributes({
     *     "type":"button",
     *     "class":"govuk-button govuk-button--secondary",
     *     "id": "preview"
     * })
     * @Form\Options({
     *     "label": "Preview",
     *     "keepForReadOnly": true,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $preview = null;

    /**
     * VOL-7238: "Send test via Notify" button. Only rendered for md templates when the env
     * has a notify_test DSN configured — TemplateController::alterFormForEdit removes this
     * element otherwise so it never appears on html/plain rows or in cut-over production envs.
     *
     * @Form\Attributes({
     *     "type":"button",
     *     "class":"govuk-button govuk-button--secondary",
     *     "id": "sendTestViaNotify"
     * })
     * @Form\Options({
     *     "label": "Send test via Notify",
     *     "keepForReadOnly": true,
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $sendTestViaNotify = null;
}

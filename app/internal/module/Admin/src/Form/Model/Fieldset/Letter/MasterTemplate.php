<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class MasterTemplate
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Options({"label": "Name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $name = null;

    /**
     * Page-chrome HTML shell with {{SLOT}} placeholders. Advanced — rarely edited.
     * VOL-7305: switched from EditorJs to Textarea because the field's content is
     * consumed as raw HTML by populateTemplate(); the EditorJs widget would have
     * produced incompatible JSON.
     *
     * @Form\Options({
     *     "label": "Page chrome HTML (advanced)",
     *     "hint": "HTML shell with {{TOKENS}} placeholders. Rarely edited — the day-to-day chrome content lives in the slot fields below.",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"id":"templateContent", "class":"extra-long", "rows":12, "name":"templateContent"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $templateContent = null;

    /**
     * VOL-7305: chrome slot — top-left header (typically the agency logo via [[OTC_LOGO]]).
     *
     * @Form\Options({
     *     "label": "Header (left)",
     *     "hint": "Top-left of the letter — usually the agency logo. Use [[OTC_LOGO]] to embed the region-appropriate logo automatically."
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"headerLeftContent", "class":"extra-long", "name":"headerLeftContent"})
     */
    public $headerLeftContent = null;

    /**
     * VOL-7305: chrome slot — top-right header (sender address block + contact details).
     *
     * @Form\Options({
     *     "label": "Header (right)",
     *     "hint": "Top-right of the letter — typically the sender address block. Vary per locale (en_GB / en_NI) for region-specific addresses."
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"headerRightContent", "class":"extra-long", "name":"headerRightContent"})
     */
    public $headerRightContent = null;

    /**
     * VOL-7305: chrome slot — signoff/sign-off block above the appendices.
     *
     * @Form\Options({
     *     "label": "Signoff",
     *     "hint": "Closing salutation and signature line. Use [[CASEWORKER_NAME]] to insert the issuing caseworker's name."
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"signoffContent", "class":"extra-long", "name":"signoffContent"})
     */
    public $signoffContent = null;

    /**
     * VOL-7305: chrome slot — footer at the very bottom of the letter body.
     *
     * @Form\Options({
     *     "label": "Footer",
     *     "hint": "Single-line footer note that appears at the bottom of the letter."
     * })
     * @Form\Required(false)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"footerContent", "class":"extra-long", "name":"footerContent"})
     */
    public $footerContent = null;

    /**
     * @Form\Options({
     *     "label": "Is Default",
     *     "checked_value": "1",
     *     "unchecked_value": "0"
     * })
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"", "id":"isDefault"})
     */
    public $isDefault = null;

    /**
     * @Form\Options({
     *     "label": "Locale",
     *     "hint": "Chrome variant key — en_GB (default), en_NI (NI letters), cy_GB (Welsh), or customN_GB / customN_NI for one-off chromes. Picked at letter-generation time by isNi."
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":20})
     */
    public $locale = null;
}

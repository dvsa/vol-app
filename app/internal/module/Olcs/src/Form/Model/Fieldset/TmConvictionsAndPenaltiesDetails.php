<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-convictions-and-penalties-details")
 */
class TmConvictionsAndPenaltiesDetails
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
     * @Form\Required(true)
     * @Form\Attributes({"id":"conviction-date","required":false,"class":"long"})
     * @Form\Options({
     *     "label": "internal.transport-manager.convictions-and-penalties.form.conviction-date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Validator({"name":"\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $convictionDate = null;

    /**
     * @Form\Attributes({"class":"long","id":"category-text"})
     * @Form\Options({"label":"internal.transport-manager.convictions-and-penalties.form.offence"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $categoryText = null;

    /**
     * @Form\Attributes({"class":"long","id":"notes"})
     * @Form\Options({"label":"internal.transport-manager.convictions-and-penalties.form.offence-details"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $notes = null;

    /**
     * @Form\Attributes({"class":"long","id":"court-fpn"})
     * @Form\Options({"label":"internal.transport-manager.convictions-and-penalties.form.court-fpn"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $courtFpn = null;

    /**
     * @Form\Attributes({"class":"long","id":"penalty"})
     * @Form\Options({"label":"internal.transport-manager.convictions-and-penalties.form.penalty"})
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $penalty = null;
}

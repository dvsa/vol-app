<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Interim actions
 *
 * @Form\Attributes({"class":""})
 * @Form\Name("form-actions")
 */
class InterimActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_save",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"grant"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_grant",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $grant = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"refuse"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_refuse",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $refuse = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"cancel"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_cancel",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"reprint"})
     * @Form\Options({
     *     "label": "internal.interim.form.interim_reprint",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $reprint = null;
}

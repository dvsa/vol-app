<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class Trailer
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label": "licence_goods-trailers_trailer.form.add.trailernumber",
     *     "hint": "licence_goods-trailers_trailer.form.add.trailernumber.hint"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\I18n\Validator\Alnum")
     */
    public $trailerNo;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\IsLongerSemiTrailer")
     * @Form\Options({
     *     "label": "licence_goods-trailers_trailer.form.add.islongersemitrailer.label",
     *     "hint": "licence_goods-trailers_trailer.form.add.islongersemitrailer.hint"
     * })
     */
    public $longerSemiTrailer;
}

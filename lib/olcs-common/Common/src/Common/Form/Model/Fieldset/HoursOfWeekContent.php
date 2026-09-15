<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("hoursOfWeekContent")
 * @Form\Type("Laminas\Form\Fieldset")
 */
class HoursOfWeekContent
{
    /**
     * @Form\Required(false)
     * @Form\Filter("\Laminas\Filter\StringTrim")
     * @Form\Attributes({
     *     "class": "short",
     *     "data-container-class": "inline-text",
     *     "id": "hoursMon"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-mon"
     * })
     * @Form\Validator("Laminas\I18n\Validator\IsFloat", options={
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     *})
     * @Form\Validator("Between", options={
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Mon must be between '%min%' and '%max%', inclusively"
     *     }
     *})
     */
    public $hoursMon;

    /**
     * @Form\Required(false)
     * @Form\Filter("\Laminas\Filter\StringTrim")
     * @Form\Attributes({
     *     "class": "short",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-tue"
     * })
     * @Form\Validator("Laminas\I18n\Validator\IsFloat", options={
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     *})
     * @Form\Validator("Between", options={
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Tue must be between '%min%' and '%max%', inclusively"
     *     }
     *})
     */
    public $hoursTue;

    /**
     * @Form\Required(false)
     * @Form\Filter("\Laminas\Filter\StringTrim")
     * @Form\Attributes({
     *     "class": "short",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-wed"
     * })
     * @Form\Validator("Laminas\I18n\Validator\IsFloat", options={
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     *})
     * @Form\Validator("Between", options={
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Wed must be between '%min%' and '%max%', inclusively"
     *     }
     *})
     */
    public $hoursWed;

    /**
     * @Form\Required(false)
     * @Form\Filter("\Laminas\Filter\StringTrim")
     * @Form\Attributes({
     *     "class": "short",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-thu"
     * })
     * @Form\Validator("Laminas\I18n\Validator\IsFloat", options={
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     *})
     * @Form\Validator("Between", options={
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Thu must be between '%min%' and '%max%', inclusively"
     *     }
     *})
     */
    public $hoursThu;

    /**
     * @Form\Required(false)
     * @Form\Filter("\Laminas\Filter\StringTrim")
     * @Form\Attributes({
     *     "class": "short",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-fri"
     * })
     * @Form\Validator("Laminas\I18n\Validator\IsFloat", options={
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     *})
     * @Form\Validator("Between", options={
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Fri must be between '%min%' and '%max%', inclusively"
     *     }
     *})
     */
    public $hoursFri;

    /**
     * @Form\Required(false)
     * @Form\Filter("\Laminas\Filter\StringTrim")
     * @Form\Attributes({
     *     "class": "short",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-sat"
     * })
     * @Form\Validator("Laminas\I18n\Validator\IsFloat", options={
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     *})
     * @Form\Validator("Between", options={
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Sat must be between '%min%' and '%max%', inclusively"
     *     }
     *})
     */
    public $hoursSat;

    /**
     * @Form\Required(false)
     * @Form\Filter("\Laminas\Filter\StringTrim")
     * @Form\Attributes({
     *     "class": "short",
     *     "data-container-class": "inline-text"
     * })
     * @Form\Options({
     *     "label": "days-of-week-short-sun"
     * })
     * @Form\Validator("Laminas\I18n\Validator\IsFloat", options={
     *     "messages": {
     *          "notFloat": "Only numbers are allowed"
     *     }
     *})
     * @Form\Validator("Between", options={
     *     "min": 0,
     *     "max": 24,
     *     "messages": {
     *         "notBetween": "Sun must be between '%min%' and '%max%', inclusively"
     *     }
     *})
     */
    public $hoursSun;
}

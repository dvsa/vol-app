<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form\Letter;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("letter-test-data")
 * @Form\Attributes({"method": "post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label": "Add Test Data"})
 */
class LetterTestDataAdd
{
    /**
     * @Form\Name("letterTestData")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\Letter\LetterTestData")
     */
    public $letterTestData = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}

<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset\Letter;

use Laminas\Form\Annotation as Form;

class LetterTodo
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    // phpcs:disable Generic.Commenting.Todo.TaskFound
    /**
     * @Form\Options({"label": "Todo Key"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium", "required": true})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    // phpcs:enable Generic.Commenting.Todo.TaskFound
    public $todoKey = null;

    /**
     * @Form\Options({
     *     "label": "Description",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(true)
     * @Form\Type("EditorJs")
     * @Form\Attributes({"id":"description", "class":"extra-long", "name":"description"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $description = null;

    /**
     * @Form\Options({
     *     "label": "Help Text",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"class":"extra-long", "rows": 3})
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $helpText = null;
}

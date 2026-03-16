<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form\Letter;

use Laminas\Form\Annotation as Form;

// phpcs:disable Generic.Commenting.Todo.TaskFound
/**
 * @Form\Name("letter-todo")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
// phpcs:enable Generic.Commenting.Todo.TaskFound
class LetterTodo
{
    /**
     * @Form\Name("letterTodo")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\Letter\LetterTodo")
     */
    public $letterTodo = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}

<?php

declare(strict_types=1);

namespace Common\Form;

use Laminas\Form\Form;

/**
 * A service that wraps the execution of the validation of a form.
 *
 * @see \CommonTest\Form\View\Helper\FormValidatorTest
 */
class FormValidator
{
    /**
     * Validates a form.
     *
     * @param Form $form
     */
    public function isValid(Form $form): bool
    {
        return $form->isValid();
    }
}

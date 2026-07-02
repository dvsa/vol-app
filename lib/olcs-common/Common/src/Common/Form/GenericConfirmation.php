<?php

namespace Common\Form;

/**
 * @template TFilteredValues
 * @extends Form<TFilteredValues>
 */
class GenericConfirmation extends Form
{
    /**
     * Set the label on the submit button
     *
     * @param string $label
     *
     * @return \Common\Form\GenericConfirmation
     */
    public function setSubmitLabel($label)
    {
        $this->get('form-actions')->get('submit')->setLabel($label);
        return $this;
    }

    /**
     * Remove the Cancel button
     *
     * @return \Common\Form\GenericConfirmation
     */
    public function removeCancel()
    {
        $this->get('form-actions')->remove('cancel');
        return $this;
    }

    /**
     * Set the text in the message
     *
     * @param string $message
     *
     * @return \Common\Form\GenericConfirmation
     */
    public function setMessage($message)
    {
        $this->get('messages')->get('message')->setLabel($message);
        return $this;
    }
}

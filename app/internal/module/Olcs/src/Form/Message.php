<?php

namespace Olcs\Form;

use Laminas\Form\Form;

/**
 * Message Form
 *
 * @template-extends Form<mixed>
 */
class Message extends Form
{
    /**
     * Set the message
     *
     * @param string|array $text if array, each array element will be treated as a line
     */
    public function setMessage($text)
    {
        if (is_array($text)) {
            // if array has text keys/indexes then use those as the messages
            if (array_key_exists(0, $text)) {
                $this->get('messages')->get('message')->setTokens($text);
            } else {
                $this->get('messages')->get('message')->setTokens(array_keys($text));
            }
            // use value and tokens, so that strings get translated when inserted
            $value = str_repeat('%s<br>', count($text));
            $this->get('messages')->get('message')->setValue($value);
        } else {
            $this->get('messages')->get('message')->setValue($text);
        }
    }

    /**
     * Set the label of the Ok button
     *
     * @param string $label
     */
    public function setOkButtonLabel($label)
    {
        $this->get('form-actions')->get('ok')->setLabel($label);
    }

    /**
     * Remove the Ok button
     */
    public function removeOkButton()
    {
        $this->get('form-actions')->remove('ok');
    }
}

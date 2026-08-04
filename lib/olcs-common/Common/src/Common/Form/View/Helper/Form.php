<?php

namespace Common\Form\View\Helper;

use Laminas\Form\FormInterface as LaminasFormInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Element\Hidden;

/**
 * Form element view helper
 */
class Form extends \Laminas\Form\View\Helper\Form
{
    /**
     * Render a form from the provided $form
     *  We override the parent here as we want to
     *   a. Add logging
     *   b. Ensure fieldsets come before elements
     *
     * @param LaminasFormInterface $form            Form
     * @param bool              $includeFormTags Is include form tags
     */
    #[\Override]
    public function render(LaminasFormInterface $form, $includeFormTags = true): string
    {
        if (method_exists($form, 'prepare')) {
            $form->prepare();
        }
        $elements = [];
        $hiddenSubmitElement = '';

        /** @var \Laminas\View\Renderer\PhpRenderer $view */
        $view = $this->getView();

        if ($form->getAttribute('action') === null) {
            // set the action, point being to remove any anchor (eg #validation-summary) from the URL
            $form->setAttribute('action', $_SERVER['REQUEST_URI']);
        }

        $view->formCollection()->setReadOnly($form->getOption('readonly'));

        /** @var callable $rowHelper */
        $rowHelper = (
            $form->getOption('readonly') ?
            $view->plugin('readonlyformrow') :
            $view->plugin('formrow')
        );

        /** @var \Laminas\Form\ElementInterface|FieldsetInterface $element */
        foreach ($form as $element) {
            if ($element instanceof FieldsetInterface) {
                $canKeepEmptyFieldset = $element->hasAttribute('keepEmptyFieldset') && (bool)$element->getAttribute('keepEmptyFieldset');

                // do not display empty fieldsets as per OLCS-12318
                if (!$element->count() && !$canKeepEmptyFieldset) {
                    continue;
                }

                if ($this->isAllChildsHidden($element) === true) {
                    $element->setAttribute('class', 'hidden');
                }

                // In relation to: OLCS-16809 and OLCS-16630.  Read comments.
                // Not the best method, but the table element is treated as a fieldset.
                // So InputFilter does not set messages on isValid().
                // Now the validation is done, we manipulate where the messages are shown.
                if ($element->has('rows') && $element->get('rows')->getMessages()) {
                    $errors = $element->get('rows')->getMessages();
                    $element->get('rows')->setMessages([]);
                    $element->get('table')->setMessages($errors);
                }

                $elements[] = $view->addTags(
                    $view->formCollection($element)
                );
            } elseif ($element->getName() === 'form-actions[continue]') {
                $hiddenSubmitElement = $rowHelper($element);
            } else {
                $elements[] = $rowHelper($element);
            }
        }

        return sprintf(
            '%s%s%s%s',
            $includeFormTags ? $this->openTag($form) : '',
            $hiddenSubmitElement,
            implode("\n", $elements),
            $includeFormTags ? $this->closeTag() : ''
        );
    }

    /**
     * Check is all children (and their children) is hidden
     *
     * @param FieldsetInterface $fieldset Checked fieldset elemen
     *
     * @return bool
     */
    private function isAllChildsHidden(\Laminas\Form\FieldsetInterface $fieldset)
    {
        //  iterate by elements
        /** @var \Laminas\Form\Element $element */
        foreach ($fieldset->getElements() as $element) {
            if (!$element instanceof Hidden) {
                return false;
            }
        }

        //  iterate by child fieldsets
        /** @var \Laminas\Form\FieldsetInterface $child */
        foreach ($fieldset->getFieldsets() as $child) {
            if ($this->isAllChildsHidden($child) === false) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace Olcs\Form\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Form\ElementInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\Form\Exception;
use Olcs\Form\Element\SubmissionSections  as SubmissionSectionsElement;

/**
 * View helper to render the submission sections element
 *
 */
class SubmissionSections extends AbstractHelper
{
    /**
     * Render a SubmissionSections element from the provided $element
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function __invoke(ElementInterface $element)
    {
        if (!$element instanceof SubmissionSectionsElement) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s requires that the element is of type Olcs\Form\Element\SubmissionSections',
                    __METHOD__
                )
            );
        }

        $formSelectPlugin = $this->view->plugin('formSelect');
        $buttonPlugin = $this->view->plugin('formButton');
        $hiddenPlugin = $this->view->plugin('formHidden');

        $multiCheckboxPlugin = $this->view->plugin('formMultiCheckbox');

        $multiCheckboxPlugin->setSeparator('</div><div class="field--two-col">');
        $markup = $formSelectPlugin->render($element->getSubmissionType()) . '<br /><br />' .
                '<div class="field--two-col">' .
                    $multiCheckboxPlugin->render($element->getSections()) .
                '</div>' .
                $hiddenPlugin->render($element->getTransportManager());
        $multiCheckboxPlugin->setSeparator('');

        return $markup;
    }
}

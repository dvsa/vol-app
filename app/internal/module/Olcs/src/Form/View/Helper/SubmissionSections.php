<?php

namespace Olcs\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\Form\Exception;

/**
 * View helper to render the submission sections element
 *
 */
class SubmissionSections extends AbstractHelper
{

    /**
     * Render a SubmissionSections element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function __invoke()
    {
        echo 'here';exit;
        return 'submissionSections helper invoked';
/*        if (!$element instanceof SubmissionSections) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Olcs\Form\Element\SubmissionSections',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }
*/
    }

}

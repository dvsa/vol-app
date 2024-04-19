<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\TranslateableTextHandler;
use RuntimeException;

class GuidanceTemplateVarsAdder
{
    /**
     * Create service instance
     *
     * @return GuidanceTemplateVarsAdder
     */
    public function __construct(private TranslateableTextHandler $translateableTextHandler)
    {
    }

    /**
     * Conditionally append the template data representing the guidance/additional guidance
     *
     * @param string $arrayKey
     *
     * @return array
     */
    public function add(array $templateVars, array $questionText, $arrayKey)
    {
        if (!isset($questionText[$arrayKey])) {
            return $templateVars;
        }

        $guidance = $questionText[$arrayKey];
        $filter = $guidance['filter'];
        $valueToAppend = match ($filter) {
            'raw' => [
                'disableHtmlEscape' => true,
                'value' => $this->translateableTextHandler->translate($guidance['translateableText'])
            ],
            'htmlEscape' => $this->translateableTextHandler->translate($guidance['translateableText']),
            default => throw new RuntimeException('Unhandled filter name ' . $filter),
        };

        $templateVars[$arrayKey] = $valueToAppend;

        return $templateVars;
    }
}

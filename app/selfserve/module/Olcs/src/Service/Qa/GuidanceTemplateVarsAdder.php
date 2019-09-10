<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\TranslateableTextHandler;
use RuntimeException;

class GuidanceTemplateVarsAdder
{
    /** @var TranslateableTextHandler */
    private $translateableTextHandler;

    /**
     * Create service instance
     *
     * @return GuidanceTemplateVarsAdder
     */
    public function __construct(TranslateableTextHandler $translateableTextHandler)
    {
        $this->translateableTextHandler = $translateableTextHandler;
    }

    /**
     * Conditionally append the template data representing the guidance/additional guidance
     *
     * @param array $templateVars
     * @param array $questionText
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
        switch ($filter) {
            case 'raw':
                $valueToAppend = [
                    'disableHtmlEscape' => true,
                    'value' => $this->translateableTextHandler->translate($guidance['translateableText'])
                ];
                break;
            case 'htmlEscape':
                $valueToAppend = $this->translateableTextHandler->translate($guidance['translateableText']);
                break;
            default:
                throw new RuntimeException('Unhandled filter name ' . $filter);
        }

        $templateVars[$arrayKey] = $valueToAppend;

        return $templateVars;
    }
}

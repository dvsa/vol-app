<?php

namespace Olcs\Service\Qa;

class TemplateVarsGenerator
{
    /**
     * Create service instance
     *
     *
     * @return TemplateVarsGenerator
     */
    public function __construct(protected QuestionArrayProvider $questionArrayProvider, protected GuidanceTemplateVarsAdder $guidanceTemplateVarsAdder)
    {
    }

    /**
     * Get the template variables corresponding to the provided question text data
     *
     *
     * @return array
     */
    public function generate(array $questionText)
    {
        $templateVars = $this->questionArrayProvider->get($questionText['question']);
        $templateVars = $this->guidanceTemplateVarsAdder->add($templateVars, $questionText, 'guidance');
        $templateVars = $this->guidanceTemplateVarsAdder->add($templateVars, $questionText, 'additionalGuidance');

        return $templateVars;
    }
}

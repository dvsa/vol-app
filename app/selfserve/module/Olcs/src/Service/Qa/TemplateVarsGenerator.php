<?php

namespace Olcs\Service\Qa;

class TemplateVarsGenerator
{
    /**
     * Create service instance
     *
     * @param QuestionArrayProvider $questionArrayProvider
     * @param GuidanceTemplateVarsAdder $guidanceTemplateVarsAdder
     *
     * @return TemplateVarsGenerator
     */
    public function __construct(
        QuestionArrayProvider $questionArrayProvider,
        GuidanceTemplateVarsAdder $guidanceTemplateVarsAdder
    ) {
        $this->questionArrayProvider = $questionArrayProvider;
        $this->guidanceTemplateVarsAdder = $guidanceTemplateVarsAdder;
    }

    /**
     * Get the template variables corresponding to the provided question text data
     *
     * @param array $questionText
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

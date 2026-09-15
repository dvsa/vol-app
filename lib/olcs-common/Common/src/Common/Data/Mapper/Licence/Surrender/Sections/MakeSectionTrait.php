<?php

namespace Common\Data\Mapper\Licence\Surrender\Sections;

trait MakeSectionTrait
{
    protected $displayChangeLinkInHeading = true;

    /**
     * @return (array|mixed|string)[]
     *
     * @psalm-return array{sectionHeading: string, changeLinkInHeading: mixed, change: mixed, questions: array}
     */
    public function makeSection(): array
    {
        $questions = $this->makeQuestions();

        return [
            'sectionHeading' => $this->translator->translate($this->heading),
            'changeLinkInHeading' => $this->displayChangeLinkInHeading,
            'change' => $this->makeChangeLink(),
            'questions' => $questions
        ];
    }

    abstract protected function makeChangeLink();

    public function setDisplayChangeLinkInHeading(bool $displayChangeLinkInHeading): void
    {
        $this->displayChangeLinkInHeading = $displayChangeLinkInHeading;
    }
}

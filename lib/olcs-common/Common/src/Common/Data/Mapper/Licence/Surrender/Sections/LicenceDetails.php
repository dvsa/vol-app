<?php

namespace Common\Data\Mapper\Licence\Surrender\Sections;

class LicenceDetails extends AbstractSection
{
    public $licence;
    protected $heading = 'licence-details';

    /**
     * @return (mixed|string)[][]
     *
     * @psalm-return list{0: array{label: string, answer: mixed, changeLinkInHeading: mixed}, 1: array{label: string, answer: mixed, changeLinkInHeading: mixed}, 2?: array{label: string, answer: string, changeLinkInHeading: mixed}}
     */
    #[\Override]
    protected function makeQuestions()
    {

        $questions = [];

        $questions[] = [
            'label' => $this->translator->translate('licence-number-full'),
            'answer' => $this->licence['licNo'],
            'changeLinkInHeading' => $this->displayChangeLinkInHeading
        ];

        $questions[] = [
            'label' => $this->translator->translate('name-of-licence-holder'),
            'answer' => $this->licence['organisation']['name'],
            'changeLinkInHeading' => $this->displayChangeLinkInHeading
        ];

        if (!empty($this->licence['organisation']['tradingNames'])) {
            $tradingNames = [];
            foreach ($this->licence['organisation']['tradingNames'] as $tradingData) {
                $tradingNames[] = $tradingData['name'];
            }

            $tradingName = implode(',<br>', $tradingNames);
            $tradingNameLabel = count($tradingNames) > 1 ? 'trading-names' : 'trading-name';

            $questions[] = [
                'label' => $this->translator->translate($tradingNameLabel),
                'answer' => $tradingName,
                'changeLinkInHeading' => $this->displayChangeLinkInHeading
            ];
        }

        return $questions;
    }

    /**
     * @return false
     */
    #[\Override]
    protected function makeChangeLink()
    {
        return false;
    }
}

<?php

namespace Common\Data\Mapper\Licence\Surrender\Sections;

class CorrespondenceAddress extends AbstractSection
{
    public $licence;
    protected $heading = 'correspondence-address';

    /**
     * @return (mixed|string)[][]
     *
     * @psalm-return list{array{label: string, answer: string, changeLinkInHeading: mixed}, array{label: string, answer: mixed, changeLinkInHeading: mixed}, array{label: string, answer: mixed, changeLinkInHeading: mixed}}
     */
    #[\Override]
    protected function makeQuestions()
    {

        $questions = [];

        $address = '';

        for ($n = 1; $n <= 4; ++$n) {
            $addressLine = trim($this->licence['correspondenceCd']['address']['addressLine' . $n]);
            if (strlen($addressLine) > 0) {
                $address .= strlen($address) > 0 ? "<br>" . $addressLine : $addressLine;
            }
        }

        $questions[] = [
            'label' => $this->translator->translate('address'),
            'answer' => $address,
            'changeLinkInHeading' => $this->displayChangeLinkInHeading
        ];

        $questions[] = [
            'label' => $this->translator->translate('address_townCity'),
            'answer' => $this->licence['correspondenceCd']['address']['town'],
            'changeLinkInHeading' => $this->displayChangeLinkInHeading
        ];

        $questions[] = [
            'label' => $this->translator->translate('address_country'),
            'answer' => $this->licence['correspondenceCd']['address']['countryCode']['countryDesc'],
            'changeLinkInHeading' => $this->displayChangeLinkInHeading
        ];

        return $questions;
    }

    /**
     * @return string[]
     *
     * @psalm-return array{sectionLink: string}
     */
    #[\Override]
    protected function makeChangeLink()
    {
        return [
            'sectionLink' => $this->urlHelper->fromRoute('licence/surrender/address-details', [], [], true) .
                '#correspondenceAddress'
        ];
    }
}

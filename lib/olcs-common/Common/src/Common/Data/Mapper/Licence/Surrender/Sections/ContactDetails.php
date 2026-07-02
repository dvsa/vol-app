<?php

namespace Common\Data\Mapper\Licence\Surrender\Sections;

class ContactDetails extends AbstractSection
{
    public $licence;
    protected $heading = 'contact-details';

    /**
     * @return (mixed|string)[][]
     *
     * @psalm-return list{array{label: string, answer: ''|mixed, changeLinkInHeading: mixed}, array{label: string, answer: ''|mixed, changeLinkInHeading: mixed}, array{label: string, answer: mixed, changeLinkInHeading: mixed}}
     */
    #[\Override]
    protected function makeQuestions()
    {

        $questions = [];

        $primaryPhone = '';
        $secondaryPhone = '';

        foreach ($this->licence['correspondenceCd']['phoneContacts'] as $phoneContact) {
            if ($phoneContact['phoneContactType']['id'] === 'phone_t_primary') {
                $primaryPhone = $phoneContact['phoneNumber'];
            } elseif ($phoneContact['phoneContactType']['id'] === 'phone_t_secondary') {
                $secondaryPhone = $phoneContact['phoneNumber'];
            }
        }

        $questions[] = [
            'label' => $this->translator->translate('contact-number'),
            'answer' => $primaryPhone,
            'changeLinkInHeading' => $this->displayChangeLinkInHeading
        ];

        $questions[] = [
            'label' => $this->translator->translate('secondary-contact-number'),
            'answer' => $secondaryPhone,
            'changeLinkInHeading' => $this->displayChangeLinkInHeading
        ];

        $questions[] = [
            'label' => $this->translator->translate('Email'),
            'answer' => $this->licence['correspondenceCd']['emailAddress'],
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
                '#contactDetails'
        ];
    }
}

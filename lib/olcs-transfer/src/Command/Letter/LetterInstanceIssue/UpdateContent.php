<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceIssue;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

#[Transfer\RouteName('backend/letter/letter-instance-issue/single')]
#[Transfer\Method('PUT')]
final class UpdateContent extends AbstractCommand
{
    use Identity;

    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 2])]
    #[Transfer\Escape(false)]
    protected $editedContent;

    /**
     * Caseworker-written heading, only used on an Other issue (VOL-7735). Not sent = leave as is.
     * Plain text, escaped wherever it's shown, so not tag-stripped: that would cut "<5 vehicles".
     *
     * @var string|null
     */
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['max' => 255])]
    #[Transfer\Escape(false)]
    protected $editedHeading;

    /**
     * @var int
     */
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $version;

    /**
     * @return string
     */
    public function getEditedContent()
    {
        return $this->editedContent;
    }

    /**
     * @return string|null
     */
    public function getEditedHeading()
    {
        return $this->editedHeading;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}

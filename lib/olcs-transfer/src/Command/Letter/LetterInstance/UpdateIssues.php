<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Change the issues on a letter that has already been generated (VOL-7734)
 */
#[Transfer\RouteName('backend/letter/letter-instance/update-issues')]
#[Transfer\Method('POST')]
final class UpdateIssues extends AbstractCommand
{
    use Identity;

    /**
     * LetterIssue ids wanted on the letter, in the order they should appear
     *
     * @var array|null
     */
    #[Transfer\Optional]
    #[Transfer\ArrayInput]
    protected $selectedIssues;

    /**
     * Go ahead even though the caseworker's edits on removed issues or to-dos will be lost
     *
     * @var bool|null
     */
    #[Transfer\Filter('Laminas\Filter\Boolean')]
    #[Transfer\Optional]
    protected $confirmLoss;

    /**
     * @return array|null
     */
    public function getSelectedIssues()
    {
        return $this->selectedIssues;
    }

    /**
     * @return bool|null
     */
    public function getConfirmLoss()
    {
        return $this->confirmLoss;
    }
}

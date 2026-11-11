<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterIssueType Entity
 */
#[ORM\Table(name: 'letter_issue_type')]
#[ORM\Entity]
class LetterIssueType extends AbstractLetterIssueType
{
    /**
     * The "Other issues" type at the end of the issues, seeded by olcs-etl (VOL-7735)
     */
    public const CODE_OTHER = 'OT';
}

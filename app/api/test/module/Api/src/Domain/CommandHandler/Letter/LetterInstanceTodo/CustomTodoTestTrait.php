<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * A letter with one issue, for the custom to-do commands (VOL-7735)
 */
trait CustomTodoTestTrait
{
    private function issueOnLetter(bool $isOther, string $status = LetterInstance::STATUS_DRAFT): LetterInstanceIssue
    {
        $letter = new LetterInstance();
        $letter->setStatus(new RefData($status));

        $version = new LetterIssueVersion();
        $version->setHeading('Other issue');
        $version->setIsOther($isOther);

        $issue = new LetterInstanceIssue();
        $issue->setId(10);
        $issue->setLetterIssueVersion($version);
        $letter->addLetterInstanceIssue($issue);

        return $issue;
    }
}

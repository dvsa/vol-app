<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;

/**
 * A sent (or failed, or cancelled) letter's content is what the operator received, or was meant
 * to; keep it as it is. Only a draft or ready letter can be changed.
 */
trait LetterOpenForChangesTrait
{
    /**
     * @throws ValidationException
     */
    private function assertLetterOpenForChanges(?LetterInstance $letterInstance): void
    {
        if ($letterInstance !== null && !$letterInstance->isOpenForChanges()) {
            throw new ValidationException(['Only a draft or ready letter can be changed']);
        }
    }
}

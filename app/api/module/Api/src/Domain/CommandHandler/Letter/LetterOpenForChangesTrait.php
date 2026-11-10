<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;

/**
 * A sent (or failed, or cancelled) letter's content is what the operator received, or was meant
 * to; keep it as it is. Only a draft or ready letter can be changed, and changing a ready
 * (prepared) one takes it back to draft.
 */
trait LetterOpenForChangesTrait
{
    /**
     * Check the letter can be changed, and take it back to draft if it's been prepared
     *
     * @throws ValidationException
     */
    private function changeLetter(?LetterInstance $letterInstance): void
    {
        $this->assertLetterOpenForChanges($letterInstance);
        $this->takeBackToDraft($letterInstance);
    }

    /**
     * @throws ValidationException
     */
    private function assertLetterOpenForChanges(?LetterInstance $letterInstance): void
    {
        if ($letterInstance !== null && !$letterInstance->isOpenForChanges()) {
            throw new ValidationException(['Only a draft or ready letter can be changed']);
        }
    }

    /**
     * A prepared letter's PDF no longer matches once it changes, so it goes back to draft and has
     * to be prepared again (with the input required check) before it can be sent. This also writes
     * the versioned letter row, so a change racing the send fails rather than landing after it.
     */
    private function takeBackToDraft(?LetterInstance $letterInstance): void
    {
        if ($letterInstance !== null && $letterInstance->isReady()) {
            $letterInstance->setStatus($this->getRepo()->getRefdataReference(LetterInstance::STATUS_DRAFT));
        }
    }
}

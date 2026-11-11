<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter;

use Doctrine\ORM\OptimisticLockException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;

/**
 * The rules every change to a letter follows.
 *
 * A sent (or cancelled) letter's content is what the operator received, or was meant to; keep it
 * as it is. Only a draft or ready letter can be changed.
 *
 * Every change writes the letter's own versioned row, so changes, preparing the letter and sending
 * it happen one at a time: whichever lands second fails and changes nothing. Without that, a change
 * to a draft could land while its PDF was being prepared (a ready letter whose PDF is missing it),
 * or just after it was sent.
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

    /**
     * Record a change to the letter, once it's been checked as open for changes. A change to what
     * it says takes a prepared letter back to draft: its PDF no longer matches, so it has to be
     * prepared again (with the input required check) before it can be sent.
     */
    private function recordLetterChange(?LetterInstance $letterInstance, bool $wordingChanged = true): void
    {
        if ($letterInstance === null) {
            return;
        }

        // writes the letter's own row even when only one of its parts changed
        $letterInstance->setLastModifiedOn(new \DateTime());

        if ($wordingChanged && $letterInstance->isReady()) {
            $letterInstance->setStatus($this->getRepo()->getRefdataReference(LetterInstance::STATUS_DRAFT));
        }
    }

    /**
     * Save a change to a letter. One that clashes with another change to the same letter made at
     * the same moment (from another tab, or preparing or sending it) changes nothing and says so.
     *
     * @param callable(): mixed $save
     * @throws ValidationException
     */
    private function saveLetterChange(callable $save): void
    {
        try {
            $save();
        } catch (OptimisticLockException) {
            throw new ValidationException([
                'This letter was changed somewhere else at the same time, so this didn\'t go through. '
                . 'Reload the page and try again.',
            ]);
        }
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter;

use Doctrine\ORM\OptimisticLockException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Helpers for the rules every change to a letter follows (LetterOpenForChangesTrait)
 */
trait LetterChangeTestTrait
{
    private function letterWithStatus(string $status): LetterInstance
    {
        $letter = new LetterInstance();
        $letter->setStatus(new RefData($status));

        return $letter;
    }

    /**
     * EditorJS content with one paragraph, as the edit page posts it
     */
    private static function editorJson(string $text, int $time = 1): string
    {
        return (string) json_encode(['time' => $time, 'blocks' => [['id' => 'b' . $time, 'type' => 'paragraph', 'data' => ['text' => $text]]]]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function editorContent(string $text): array
    {
        return ['blocks' => [['type' => 'paragraph', 'data' => ['text' => $text]]]];
    }

    /**
     * The letter's own versioned row is written with the change, so a change, preparing it and
     * sending it can't land on top of each other
     */
    private function assertLetterRowWritten(LetterInstance $letter): void
    {
        $this->assertInstanceOf(\DateTimeInterface::class, $letter->getLastModifiedOn(), 'the letter row is written');
    }

    private function expectClashMessage(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This letter was changed somewhere else at the same time');
    }

    private static function clash(): OptimisticLockException
    {
        return new OptimisticLockException('The optimistic lock on an entity failed.', null);
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterOpenForChangesTrait;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Every change to a letter writes the letter's own versioned row, even when only one of its parts
 * changed (VOL-7735), so a change can't land on top of preparing or sending it: whichever comes
 * second fails and changes nothing. Checked with the rule the command handlers use
 * (LetterOpenForChangesTrait), through the real mapping and real flushes.
 */
final class LetterChangeClashTest extends IntegrationTestCase
{
    /**
     * @return array{letter: int, todo: int}
     */
    private function letterWithACustomTodo(): array
    {
        $conn = $this->em()->getConnection();
        $licence = $conn->fetchAssociative(
            'SELECT id, organisation_id FROM licence WHERE organisation_id IS NOT NULL ORDER BY id LIMIT 1'
        );
        $issueVersionId = (int) $conn->fetchOne('SELECT MIN(id) FROM letter_issue_version');
        $this->assertIsArray($licence, 'Expected the seeded test dataset to contain a licence');
        $this->assertGreaterThan(0, $issueVersionId, 'Expected the seeded test dataset to contain a letter issue');

        $conn->insert('letter_type', ['name' => 'ITEST 7735 clash']);
        $conn->insert('letter_instance', [
            'reference' => substr('IC' . uniqid(), 0, 20),
            'letter_type_id' => (int) $conn->lastInsertId(),
            'licence_id' => $licence['id'],
            'organisation_id' => $licence['organisation_id'],
            'status' => LetterInstance::STATUS_DRAFT,
        ]);
        $letterId = (int) $conn->lastInsertId();
        $conn->insert('letter_instance_issue', [
            'letter_instance_id' => $letterId,
            'letter_issue_version_id' => $issueVersionId,
            'display_order' => 0,
        ]);
        $conn->insert('letter_instance_todo', [
            'letter_instance_id' => $letterId,
            'letter_instance_issue_id' => (int) $conn->lastInsertId(),
            'display_order' => 0,
        ]);

        return ['letter' => $letterId, 'todo' => (int) $conn->lastInsertId()];
    }

    /**
     * The same trait the command handlers use, so these tests run the real rule
     */
    private function letterChanges(): object
    {
        return new class ($this->repo('LetterInstance')) {
            use LetterOpenForChangesTrait;

            public function __construct(private readonly object $repo)
            {
            }

            protected function getRepo(): object
            {
                return $this->repo;
            }

            public function record(LetterInstance $letterInstance): void
            {
                $this->recordLetterChange($letterInstance);
            }

            public function save(callable $save): void
            {
                $this->saveLetterChange($save);
            }
        };
    }

    /**
     * What one request does to change a to-do on a letter: the to-do changes, and the change is
     * recorded on the letter
     */
    private function changeTheTodo(EntityManager $em, int $todoId): void
    {
        $todo = $em->find(LetterInstanceTodo::class, $todoId);
        $this->assertInstanceOf(LetterInstanceTodo::class, $todo);
        $todo->setEditedDescriptionFromArray(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Changed']]]]);
        $this->letterChanges()->record($todo->getLetterInstance());
    }

    public function testChangingAPartOfADraftWritesTheLettersVersionedRow(): void
    {
        $ids = $this->letterWithACustomTodo();
        $conn = $this->em()->getConnection();
        $versionBefore = (int) $conn->fetchOne('SELECT version FROM letter_instance WHERE id = ?', [$ids['letter']]);

        $this->changeTheTodo($this->em(), $ids['todo']);
        $this->em()->flush();

        $this->assertSame(
            $versionBefore + 1,
            (int) $conn->fetchOne('SELECT version FROM letter_instance WHERE id = ?', [$ids['letter']])
        );
        $this->assertNotNull($conn->fetchOne('SELECT edited_description FROM letter_instance_todo WHERE id = ?', [$ids['todo']]));
    }

    public function testAChangeThatClashesWithAnotherChangeToTheLetterChangesNothing(): void
    {
        $ids = $this->letterWithACustomTodo();
        $conn = $this->em()->getConnection();

        // its own entity manager, like a separate request: a failed flush closes the one it's on
        $request = new EntityManager($conn, $this->em()->getConfiguration());
        $this->changeTheTodo($request, $ids['todo']);

        // meanwhile another request prepares or sends the letter
        $conn->executeStatement(
            'UPDATE letter_instance SET status = ?, version = version + 1 WHERE id = ?',
            [LetterInstance::STATUS_READY, $ids['letter']]
        );

        try {
            $this->letterChanges()->save(fn () => $request->flush());
            $this->fail('Expected the clashing change to fail');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('changed somewhere else at the same time', implode(' ', $e->getMessages()));
        }

        $this->assertNull(
            $conn->fetchOne('SELECT edited_description FROM letter_instance_todo WHERE id = ?', [$ids['todo']]),
            'the to-do is left as it was'
        );
        $this->assertSame(
            LetterInstance::STATUS_READY,
            $conn->fetchOne('SELECT status FROM letter_instance WHERE id = ?', [$ids['letter']])
        );
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Printing or emailing a letter's document marks the letter as sent (VOL-7735), found from the
 * document through the real mapping and saved through a real flush.
 */
final class LetterSentTest extends IntegrationTestCase
{
    public function testALetterIsFoundFromItsDocumentAndMarkedAsSent(): void
    {
        $conn = $this->em()->getConnection();
        $documentId = (int) $conn->fetchOne(
            'SELECT MIN(d.id) FROM document d LEFT JOIN letter_instance li ON li.document_id = d.id WHERE li.id IS NULL'
        );
        $licence = $conn->fetchAssociative(
            'SELECT id, organisation_id FROM licence WHERE organisation_id IS NOT NULL ORDER BY id LIMIT 1'
        );
        $this->assertGreaterThan(0, $documentId, 'Expected the seeded test dataset to contain a document');
        $this->assertIsArray($licence, 'Expected the seeded test dataset to contain a licence');

        $conn->insert('letter_type', ['name' => 'ITEST 7735']);
        $conn->insert('letter_instance', [
            'reference' => substr('IT' . uniqid(), 0, 20),
            'letter_type_id' => (int) $conn->lastInsertId(),
            'licence_id' => $licence['id'],
            'organisation_id' => $licence['organisation_id'],
            'status' => LetterInstance::STATUS_READY,
            'document_id' => $documentId,
        ]);
        $instanceId = (int) $conn->lastInsertId();

        $repo = $this->repo('LetterInstance');
        $letter = $repo->fetchByDocument($documentId);
        $this->assertInstanceOf(LetterInstance::class, $letter);
        $this->assertSame($instanceId, $letter->getId());

        $letter->markAsSent($repo->getRefdataReference(LetterInstance::STATUS_SENT));
        $repo->save($letter);
        $this->em()->clear();

        $row = $conn->fetchAssociative('SELECT status, sent_on FROM letter_instance WHERE id = ?', [$instanceId]);
        $this->assertSame(LetterInstance::STATUS_SENT, $row['status']);
        $this->assertNotNull($row['sent_on']);
        $this->assertFalse($repo->fetchById($instanceId)->isOpenForChanges());
        $this->assertNull($repo->fetchByDocument(PHP_INT_MAX), 'a document not built in the letter builder');
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterAppendix;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * fetchById() on the versioned letter repositories, run against the real ORM.
 * Doctrine ORM 3 rejects a null hydration mode, which the unit tests never see
 * because the query is mocked.
 */
class VersionedRepositoryFetchByIdTest extends IntegrationTestCase
{
    public static function repositories(): array
    {
        return [
            ['LetterIssue', LetterIssue::class],
            ['LetterAppendix', LetterAppendix::class],
            ['LetterSection', LetterSection::class],
            ['LetterTodo', LetterTodo::class],
        ];
    }

    #[DataProvider('repositories')]
    public function testFetchByIdHydratesEntityWithCurrentVersion(string $repo, string $entityClass): void
    {
        $id = $this->em()->createQuery("SELECT MIN(e.id) FROM {$entityClass} e")->getSingleScalarResult();
        $this->assertNotNull($id, "Expected the seeded test dataset to contain a {$repo}");

        $entity = $this->repo($repo)->fetchById((int) $id);

        $this->assertInstanceOf($entityClass, $entity);
        $this->assertNotNull($entity->getCurrentVersion());
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanAccessCorrelatedDocuments;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;

/**
 * Can Access Document Test
 */
class CanAccessCorrelatedDocumentsTest extends AbstractValidatorsTestCase
{
    public $cache;
    /**
     * @var CanAccessCorrelatedDocuments|m\MockInterface
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(CanAccessCorrelatedDocuments::class)
                      ->makePartial()
                      ->shouldAllowMockingProtectedMethods();

        parent::setUp();
    }

    public function testIsValidTrue(): void
    {
        $doc = m::mock(Entity\Doc\Document::class);
        $doc->shouldReceive('getCreatedBy->getId')
            ->once()
            ->andReturn(1);

        $this->auth->shouldReceive('getIdentity->getUser->getId')
                   ->once()
                   ->andReturn(1);

        $docRepo = $this->mockRepo(Repository\Document::class);
        $docRepo->shouldReceive('fetchById')
                ->once()
                ->with(1)
                ->andReturn($doc);

        $this->cache->shouldReceive('getCustomItem')
                    ->once()
                    ->with(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, '1234567890')
                    ->andReturn([1]);

        $query = Documents::create(['correlationId' => '1234567890']);
        $this->assertTrue($this->sut->isValid($query));
    }

    public function testIsValidFalse(): void
    {
        $doc = m::mock(Entity\Doc\Document::class);
        $doc->shouldReceive('getCreatedBy->getId')
            ->once()
            ->andReturn(1);

        $this->auth->shouldReceive('getIdentity->getUser->getId')
                   ->once()
                   ->andReturn(2);

        $docRepo = $this->mockRepo(Repository\Document::class);
        $docRepo->shouldReceive('fetchById')
                ->once()
                ->with(1)
                ->andReturn($doc);

        $this->cache->shouldReceive('getCustomItem')
                    ->once()
                    ->with(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, '1234567890')
                    ->andReturn([1]);

        $query = Documents::create(['correlationId' => '1234567890']);
        $this->assertFalse($this->sut->isValid($query));
    }
}

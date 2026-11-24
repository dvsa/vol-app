<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanAccessCorrelatedDocuments;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

final class CanAccessCorrelatedDocumentsTest extends AbstractHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CanAccessCorrelatedDocuments();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noCorrelationIdProvider')]
    public function testNoCorrelationIdMeansNoDocumentsToCheck(array $query): void
    {
        $cache = m::mock(CacheEncryption::class);
        $cache->expects('getCustomItem')->never();
        $this->sut->setCache($cache);

        $this->assertTrue($this->sut->isValid(Documents::create($query)));
    }

    public static function noCorrelationIdProvider(): \Iterator
    {
        yield 'not given' => [[]];
        yield 'empty' => [['correlationId' => '']];
    }
}

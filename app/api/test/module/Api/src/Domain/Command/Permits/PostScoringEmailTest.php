<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\PostScoringEmail;

/**
 * Post scoring email test
 */
final class PostScoringEmailTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = PostScoringEmail::create(
            [
                'documentIdentifier' => 'document123XYZ',
            ]
        );

        $this->assertEquals('document123XYZ', $sut->getDocumentIdentifier());
        $this->assertEquals([
            'documentIdentifier' => 'document123XYZ',
        ], $sut->getArrayCopy());
    }
}

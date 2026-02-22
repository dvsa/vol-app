<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Permits;

use Dvsa\Olcs\Api\Domain\Command\Permits\PostScoringEmail;

/**
 * Post scoring email test
 */
class PostScoringEmailTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = PostScoringEmail::create(
            [
                'documentIdentifier' => 'document123XYZ',
            ]
        );

        static::assertEquals('document123XYZ', $sut->getDocumentIdentifier());
        static::assertEquals(
            [
                'documentIdentifier' => 'document123XYZ',
            ],
            $sut->getArrayCopy()
        );
    }
}

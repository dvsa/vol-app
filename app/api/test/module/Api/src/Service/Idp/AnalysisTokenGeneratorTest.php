<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp;

use Dvsa\Olcs\Api\Service\Idp\AnalysisTokenGenerator;
use PHPUnit\Framework\TestCase;

final class AnalysisTokenGeneratorTest extends TestCase
{
    private AnalysisTokenGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new AnalysisTokenGenerator();
    }

    public function testGeneratesA16ByteBinaryTokenAndItsRfc4122Form(): void
    {
        [$binary, $string] = $this->sut->generate();

        $this->assertSame(16, strlen($binary), 'stored as BINARY(16)');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $string,
            'version 7, RFC-4122 variant'
        );
        $this->assertSame($binary, hex2bin(str_replace('-', '', $string)), 'the two forms agree');
    }

    public function testTokensAreUnique(): void
    {
        $tokens = [];

        for ($i = 0; $i < 250; $i++) {
            $tokens[] = $this->sut->generate()[1];
        }

        $this->assertCount(250, array_unique($tokens));
    }

    /** UUIDv7 is time-ordered, which is what keeps the BINARY(16) unique key index-friendly. */
    public function testTokensAreTimeOrdered(): void
    {
        $previous = $this->sut->generate()[0];

        for ($i = 0; $i < 50; $i++) {
            $current = $this->sut->generate()[0];
            $this->assertGreaterThanOrEqual(0, strcmp($current, $previous));
            $previous = $current;
        }
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp\AnalysisResultNormaliser;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliserFactory;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\ReportMapper;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\Version\VersionMapperInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Mockery as m;

/**
 * The entry point: a report in, or a stored payload of any version in, a current
 * NormalisedResult out. ReportMapper has its own tests for what the rows contain.
 */
final class AnalysisResultNormaliserTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testNormalisesAReportThroughTheReportMapper(): void
    {
        $report = ['analysis' => ['core_checks' => ['FI10' => ['result' => 'Fail', 'remark' => 'Too old.']]]];

        $normalised = (new AnalysisResultNormaliser(new ReportMapper()))->normalise($report);

        $this->assertInstanceOf(NormalisedResult::class, $normalised);
        $this->assertSame('fail', $normalised->rows()['statementDate']['flag']);
    }

    public function testAReportWithNoAnalysisNormalisesToNull(): void
    {
        $this->assertNull((new AnalysisResultNormaliser(new ReportMapper()))->normalise(['unexpected' => 'shape']));
    }

    public function testACurrentStoredPayloadComesBackUnchanged(): void
    {
        $stored = ['version' => NormalisedResult::VERSION, 'rows' => ['bank' => $this->row('Example Bank')]];

        $this->assertSame($stored, (new AnalysisResultNormaliser(new ReportMapper()))->fromStored($stored)?->toArray());
    }

    /** A row whose report could not be normalised has nothing stored. */
    public function testNothingStoredIsNull(): void
    {
        $this->assertNull((new AnalysisResultNormaliser(new ReportMapper()))->fromStored(null));
    }

    /**
     * Each mapper lifts one version to the next, so a payload two versions old passes through
     * two mappers. Registration order must not matter: each declares the version it reads.
     */
    public function testChainsVersionMappersFromTheStoredVersionUpToTheCurrentOne(): void
    {
        $current = NormalisedResult::VERSION;
        $sut = new AnalysisResultNormaliser(new ReportMapper(), [
            $this->mapperFrom($current - 1, 'second'),
            $this->mapperFrom($current - 2, 'first'),
        ]);

        $normalised = $sut->fromStored(['version' => $current - 2, 'rows' => ['bank' => $this->row('Example Bank')]]);

        $this->assertSame(
            ['bank' => $this->row('Example Bank'), 'first' => $this->row('first'), 'second' => $this->row('second')],
            $normalised?->rows()
        );
    }

    /**
     * A payload with no path to the current version is not emitted: guessing at a shape could
     * show a flag that means something else.
     */
    #[DataProvider('unplaceablePayloadProvider')]
    public function testAStoredPayloadWithoutAPathToTheCurrentVersionIsDropped(array $stored): void
    {
        $sut = new AnalysisResultNormaliser(new ReportMapper(), [
            // A gap: a mapper two versions back, but none for the version before current.
            $this->mapperFrom(NormalisedResult::VERSION - 2, 'orphaned'),
        ]);

        $this->assertNull($sut->fromStored($stored));
    }

    public static function unplaceablePayloadProvider(): array
    {
        return [
            'no version' => [['rows' => []]],
            'version is not an integer' => [['version' => '1', 'rows' => []]],
            'version with no mapper' => [['version' => NormalisedResult::VERSION - 1, 'rows' => []]],
            'version from newer code than this' => [['version' => NormalisedResult::VERSION + 1, 'rows' => []]],
            'current version without rows' => [['version' => NormalisedResult::VERSION]],
        ];
    }

    public function testRefusesTwoMappersForTheSameVersion(): void
    {
        $this->expectException(\LogicException::class);

        new AnalysisResultNormaliser(new ReportMapper(), [
            $this->mapperFrom(0, 'one'),
            $this->mapperFrom(0, 'two'),
        ]);
    }

    public function testFactoryBuildsAWorkingNormaliser(): void
    {
        $sut = (new AnalysisResultNormaliserFactory())(m::mock(ContainerInterface::class), AnalysisResultNormaliser::class);

        $stored = ['version' => NormalisedResult::VERSION, 'rows' => []];

        $this->assertSame($stored, $sut->fromStored($stored)?->toArray());
    }

    /** A fake mapper that adds a row named after itself, so the chain's order and completeness are visible. */
    private function mapperFrom(int $version, string $name): VersionMapperInterface
    {
        return new class ($version, $name) implements VersionMapperInterface {
            public function __construct(private readonly int $version, private readonly string $name)
            {
            }

            public function fromVersion(): int
            {
                return $this->version;
            }

            public function upgrade(array $payload): array
            {
                $payload['version'] = $this->version + 1;
                $payload['rows'][$this->name] = ['flag' => null, 'remark' => null, 'value' => $this->name, 'checks' => []];

                return $payload;
            }
        };
    }

    private function row(string $value): array
    {
        return ['flag' => null, 'remark' => null, 'value' => $value, 'checks' => []];
    }
}

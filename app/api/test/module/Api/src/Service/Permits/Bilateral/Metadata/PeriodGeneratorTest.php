<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\FieldsGeneratorInterface;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\PeriodGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * PeriodGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PeriodGeneratorTest extends MockeryTestCase
{
    public const BEHAVIOUR_NAME = 'behaviourName';

    public const STOCK_ID = 99;

    private $irhpPermitStockRepo;

    private $periodGenerator;

    private $fieldsGenerator;

    public function setUp(): void
    {
        $this->irhpPermitStockRepo = m::mock(IrhpPermitStockRepository::class);

        $this->periodGenerator = new PeriodGenerator($this->irhpPermitStockRepo);

        $this->periodGenerator->registerFieldsGenerator(
            'someBehaviour',
            m::mock(FieldsGeneratorInterface::class)
        );

        $this->fieldsGenerator = m::mock(FieldsGeneratorInterface::class);
        $this->periodGenerator->registerFieldsGenerator(
            self::BEHAVIOUR_NAME,
            $this->fieldsGenerator
        );

        $this->periodGenerator->registerFieldsGenerator(
            'someBehaviour',
            $this->fieldsGenerator
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $irhpPermitApplication): void
    {
        $fieldsResponse = [
            'fieldsResponseKey1' => 'fieldsResponseValue1',
            'fieldsResponseKey2' => 'fieldsResponseValue2'
        ];

        $periodNameKey = 'period.name.key';

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getPeriodNameKey')
            ->andReturn($periodNameKey);

        $this->irhpPermitStockRepo->shouldReceive('fetchById')
            ->with(self::STOCK_ID)
            ->andReturn($irhpPermitStock);

        $this->fieldsGenerator->shouldReceive('generate')
            ->with($irhpPermitStock, $irhpPermitApplication)
            ->andReturn($fieldsResponse);

        $expected = [
            'id' => self::STOCK_ID,
            'key' => $periodNameKey,
            'fields' => $fieldsResponse
        ];

        $this->assertEquals(
            $expected,
            $this->periodGenerator->generate(self::STOCK_ID, self::BEHAVIOUR_NAME, $irhpPermitApplication)
        );
    }

    public static function dpGenerate(): array
    {
        return [
            [m::mock(IrhpPermitApplication::class)],
            [null]
        ];
    }

    public function testGenerateException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No fields generator found for behaviour name unknownBehaviour');

        $this->periodGenerator->generate(self::STOCK_ID, 'unknownBehaviour', m::mock(IrhpPermitApplication::class));
    }
}

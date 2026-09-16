<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateStops;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class UpdateStopsTest extends MockeryTestCase
{
    use CommandTest;

    #[\PHPUnit\Framework\Attributes\DataProvider('providerFieldsProvider')]
    public function testProviderSelectionsCanBeClearedThroughValidation(bool $provided): void
    {
        $command = UpdateStops::create([
            'id' => 99,
            'version' => 1,
            'useAllStops' => 'N',
            'hasManoeuvre' => 'N',
            'needNewStop' => 'N',
            'hasNotFixedStop' => 'N',
            'subsidised' => 'bs_in_part',
            'subsidyDetail' => 'Existing comments',
        ] + ($provided ? ['subsidyTrafficAreas' => [], 'subsidyLocalAuthorities' => []] : []));
        $container = $this->createDtoContainer($command);
        $this->assertTrue($container->isValid());
        $this->assertSame([], $command->getSubsidyTrafficAreas());
        $this->assertSame([], $command->getSubsidyLocalAuthorities());
        $this->assertSame('Existing comments', $command->getSubsidyDetail());
    }

    public static function providerFieldsProvider(): array
    {
        return [[true], [false]];
    }

    protected function createBlankDto()
    {
        return new UpdateStops();
    }

    protected function getOptionalDtoFields()
    {
        // Array defaults are checked above; the shared optional-field tests expect null.
        return [];
    }

    protected function getValidFieldValues()
    {
        return [
            'subsidyDetail' => [str_repeat('a', 257), str_repeat('é', 1000)],
            'subsidyTrafficAreas' => [[], ['F'], ['F', 'B']],
            'subsidyLocalAuthorities' => [[], ['87'], ['87', '88']],
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'subsidyDetail' => [str_repeat('a', 1001)],
            'subsidyTrafficAreas' => [['AB']],
            'subsidyLocalAuthorities' => [['0']],
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'subsidyTrafficAreas' => [[['F', 'F'], ['F']]],
            'subsidyLocalAuthorities' => [[['87', '87'], ['87']]],
        ];
    }
}

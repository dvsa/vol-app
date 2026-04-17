<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\BilateralRequiredGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * BilateralRequiredGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class BilateralRequiredGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $permitUsageSelection, mixed $postData, mixed $expected): void
    {
        $bilateralRequiredGenerator = new BilateralRequiredGenerator();

        $this->assertEquals(
            $expected,
            $bilateralRequiredGenerator->generate($postData, $permitUsageSelection)
        );
    }

    public static function dpGenerate(): array
    {
        return [
            [
                RefData::JOURNEY_SINGLE,
                [
                    'standard-journey_single' => '4',
                    'cabotage-journey_single' => '5',
                    'standard-journey_multiple' => '',
                    'cabotage-journey_multiple' => '',
                ],
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => '4',
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => '5',
                    IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null
                ]
            ],
            [
                RefData::JOURNEY_SINGLE,
                [
                    'standard-journey_single' => '9',
                ],
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => '9',
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null,
                    IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null
                ]
            ],
            [
                RefData::JOURNEY_MULTIPLE,
                [
                    'standard-journey_single' => '',
                    'cabotage-journey_single' => '',
                    'standard-journey_multiple' => '7',
                    'cabotage-journey_multiple' => '8',
                ],
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => '7',
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => '8',
                    IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null
                ]
            ],
            [
                RefData::JOURNEY_SINGLE,
                [
                    'cabotage-journey_single' => '10',
                ],
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => '10',
                    IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null
                ]
            ],
        ];
    }
}

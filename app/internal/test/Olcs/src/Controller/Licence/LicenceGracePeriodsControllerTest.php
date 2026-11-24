<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Navigation\Navigation;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Licence\LicenceGracePeriodsController;

final class LicenceGracePeriodsControllerTest extends MockeryTestCase
{
    public function testTheTableShowsEachGracePeriodsType(): void
    {
        $sut = new LicenceGracePeriodsController(
            m::mock(NiTextTranslation::class),
            m::mock(AuthorizationService::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(TableFactory::class),
            m::mock(FormHelperService::class),
            m::mock(ScriptFactory::class),
            m::mock(Navigation::class)
        );

        $gracePeriods = [
            'results' => [
                [
                    'id' => 1,
                    'startDate' => '2026-01-01',
                    'endDate' => '2026-02-01',
                    'description' => 'typed',
                    'isActive' => true,
                    'gracePeriodType' => ['id' => 'gpt_finance', 'description' => 'Finance'],
                ],
                [
                    'id' => 2,
                    'startDate' => '2015-01-01',
                    'endDate' => '2015-02-01',
                    'description' => 'recorded before types existed',
                    'isActive' => false,
                    'gracePeriodType' => null,
                ],
            ],
        ];

        $rows = (fn (): array => $this->getTableData($gracePeriods))->call($sut);

        $this->assertSame(['Finance', 'Not set'], array_column($rows, 'gracePeriodType'));
    }
}

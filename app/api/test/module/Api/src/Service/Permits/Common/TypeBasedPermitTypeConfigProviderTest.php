<?php

namespace Dvsa\OlcsTest\Api\Service\Permits\Common;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Common\TypeBasedPermitTypeConfigProvider;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * TypeBasedPermitTypeConfigProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TypeBasedPermitTypeConfigProviderTest extends MockeryTestCase
{
    private $ecmtAnnualRestrictedCountryIds = ['FR', 'DE'];

    private $ecmtAnnualRestrictedCountriesQuestionKey = 'ecmt.annual.key';

    private $ecmtShortTermRestrictedCountryIds = ['HU' ,'RU', 'IT'];

    private $ecmtShortTermRestrictedCountriesQuestionKey = 'ecmt.short.term.key';

    private $typeBasedPermitTypeConfigProvider;

    public function setUp(): void
    {
        $config = [
            'permits' => [
                'types' => [
                    IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT => [
                        'restricted_countries_question_key' => $this->ecmtAnnualRestrictedCountriesQuestionKey,
                        'restricted_country_ids' => $this->ecmtAnnualRestrictedCountryIds,
                    ],
                    IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM => [
                        'restricted_countries_question_key' => $this->ecmtShortTermRestrictedCountriesQuestionKey,
                        'restricted_country_ids' => $this->ecmtShortTermRestrictedCountryIds,
                    ],
                ]
            ]
        ];

        $this->typeBasedPermitTypeConfigProvider = new TypeBasedPermitTypeConfigProvider($config);

        parent::setUp();
    }

    /**
     * @dataProvider dpTestGetPermitTypeConfig
     */
    public function testGetPermitTypeConfig(
        $irhpPermitTypeId,
        $excludedRestrictedCountryIds,
        $expectedRestrictedCountryIds,
        $expectedRestrictedCountriesQuestionKey
    ) {
        $permitTypeConfig = $this->typeBasedPermitTypeConfigProvider->getPermitTypeConfig($irhpPermitTypeId, $excludedRestrictedCountryIds);

        $this->assertEquals(
            $expectedRestrictedCountriesQuestionKey,
            $permitTypeConfig->getRestrictedCountriesQuestionKey()
        );

        $this->assertEquals(
            $expectedRestrictedCountryIds,
            $permitTypeConfig->getRestrictedCountryIds()
        );
    }

    public function dpTestGetPermitTypeConfig()
    {
        return [
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                [],
                $this->ecmtAnnualRestrictedCountryIds,
                $this->ecmtAnnualRestrictedCountriesQuestionKey,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                ['DE'],
                ['FR'],
                $this->ecmtAnnualRestrictedCountriesQuestionKey,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                [],
                $this->ecmtShortTermRestrictedCountryIds,
                $this->ecmtShortTermRestrictedCountriesQuestionKey,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                ['RU', 'IT'],
                ['HU'],
                $this->ecmtShortTermRestrictedCountriesQuestionKey,
            ],
        ];
    }

    public function testGetPermitTypeConfigMissingConfig()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No config found for permit type 99');

        $this->typeBasedPermitTypeConfigProvider->getPermitTypeConfig(99);
    }
}

<?php

declare(strict_types=1);

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
    private const ECMT_ANNUAL_RESTRICTED_COUNTRY_IDS = ['FR', 'DE'];

    private const ECMT_ANNUAL_RESTRICTED_COUNTRIES_QUESTION_KEY = 'ecmt.annual.key';

    private const ECMT_SHORT_TERM_RESTRICTED_COUNTRY_IDS = ['HU' ,'RU', 'IT'];

    private const ECMT_SHORT_TERM_RESTRICTED_COUNTRIES_QUESTION_KEY = 'ecmt.short.term.key';

    private $typeBasedPermitTypeConfigProvider;

    public function setUp(): void
    {
        $config = [
            'permits' => [
                'types' => [
                    IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT => [
                        'restricted_countries_question_key' => self::ECMT_ANNUAL_RESTRICTED_COUNTRIES_QUESTION_KEY,
                        'restricted_country_ids' => self::ECMT_ANNUAL_RESTRICTED_COUNTRY_IDS,
                    ],
                    IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM => [
                        'restricted_countries_question_key' => self::ECMT_SHORT_TERM_RESTRICTED_COUNTRIES_QUESTION_KEY,
                        'restricted_country_ids' => self::ECMT_SHORT_TERM_RESTRICTED_COUNTRY_IDS,
                    ],
                ]
            ]
        ];

        $this->typeBasedPermitTypeConfigProvider = new TypeBasedPermitTypeConfigProvider($config);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetPermitTypeConfig')]
    public function testGetPermitTypeConfig(
        mixed $irhpPermitTypeId,
        mixed $excludedRestrictedCountryIds,
        mixed $expectedRestrictedCountryIds,
        mixed $expectedRestrictedCountriesQuestionKey
    ): void {
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

    public static function dpTestGetPermitTypeConfig(): array
    {
        return [
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                [],
                self::ECMT_ANNUAL_RESTRICTED_COUNTRY_IDS,
                self::ECMT_ANNUAL_RESTRICTED_COUNTRIES_QUESTION_KEY,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                ['DE'],
                ['FR'],
                self::ECMT_ANNUAL_RESTRICTED_COUNTRIES_QUESTION_KEY,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                [],
                self::ECMT_SHORT_TERM_RESTRICTED_COUNTRY_IDS,
                self::ECMT_SHORT_TERM_RESTRICTED_COUNTRIES_QUESTION_KEY,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                ['RU', 'IT'],
                ['HU'],
                self::ECMT_SHORT_TERM_RESTRICTED_COUNTRIES_QUESTION_KEY,
            ],
        ];
    }

    public function testGetPermitTypeConfigMissingConfig(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No config found for permit type 99');

        $this->typeBasedPermitTypeConfigProvider->getPermitTypeConfig(99);
    }
}

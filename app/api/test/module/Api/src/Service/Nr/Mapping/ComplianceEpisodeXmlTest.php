<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\Mapping;

use Dvsa\Olcs\Api\Service\Nr\Mapping\ComplianceEpisodeXml;
use Olcs\XmlTools\Filter\MapXmlFile;

class ComplianceEpisodeXmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * loads in erru test templates from /module/Api/data/nr/ folder, tests correct data retrieval/mapping
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTemplate')]
    public function testXmlMapping(string $template): void
    {
        $domDocument = new \DOMDocument();
        $path = dirname(__DIR__) . '/../../../../../../module/Api/data/nr/' . $template;
        $domDocument->load($path);

        $sut = new ComplianceEpisodeXml(new MapXmlFile(), 'https://webgate.ec.testa.eu/move-hub/erru/3.4');

        $expected = [
            'workflowId' => '20776dc3-5fe7-42d5-b554-09ad12fa25c4',
            'memberStateCode' => 'PL',
            'sentAt' => '2014-02-20T16:22:09Z',
            'notificationNumber' => '0ffefb6b-6344-4a60-9a53-4381c32f98d9',
            'originatingAuthority' => 'Driver & Vehicle Agency',
            'communityLicenceNumber' => 'UKGB/OB1234567/00000',
            'transportUndertakingName' => 'TEST USER (SELF SERVICE)(12345)',
            'vrm' => 'aBc123',
            'checkDate' => '2014-02-20',
            'si' => [
                0 => [
                    'infringementDate' => '2014-02-20',
                    'siCategoryType' => '101',
                    'imposedErrus' => [
                        0 => [
                            'penaltyImposedIdentifier' => '1',
                            'finalDecisionDate' => '2014-02-20',
                            'siPenaltyImposedType' => '101',
                            'startDate' => '2014-03-14',
                            'endDate' => '2014-09-17',
                            'executed' => 'Yes',
                        ],
                        1 => [
                            'penaltyImposedIdentifier' => '2',
                            'finalDecisionDate' => '2014-06-25',
                            'siPenaltyImposedType' => '102',
                            'startDate' => '',
                            'endDate' => '',
                            'executed' => 'No',
                        ],
                    ],
                    'requestedErrus' => [
                        0 => [
                            'penaltyRequestedIdentifier' => '1',
                            'siPenaltyRequestedType' => '301',
                            'duration' => '12'
                        ],
                        1 => [
                            'penaltyRequestedIdentifier' => '2',
                            'siPenaltyRequestedType' => '302',
                            'duration' => '30'
                        ]
                    ]
                ],
                1 => [
                    'infringementDate' => '2014-03-21',
                    'siCategoryType' => '201',
                    'imposedErrus' => [
                        0 => [
                            'penaltyImposedIdentifier' => '3',
                            'finalDecisionDate' => '2014-03-21',
                            'siPenaltyImposedType' => '202',
                            'startDate' => '2014-04-15',
                            'endDate' => '2014-10-18',
                            'executed' => 'No',
                        ],
                        1 => [
                            'penaltyImposedIdentifier' => '4',
                            'finalDecisionDate' => '2014-07-26',
                            'siPenaltyImposedType' => '203',
                            'startDate' => '',
                            'endDate' => '',
                            'executed' => 'Yes',
                        ],
                    ],
                    'requestedErrus' => [
                        0 => [
                            'penaltyRequestedIdentifier' => '3',
                            'siPenaltyRequestedType' => '305',
                            'duration' => '18'
                        ],
                        1 => [
                            'penaltyRequestedIdentifier' => '4',
                            'siPenaltyRequestedType' => '306',
                            'duration' => '24'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $sut->mapData($domDocument));
    }

    /**
     * data provider for testXmlMapping
     */
    public static function dpTemplate(): array
    {
        return [
            ['notifyCheckResultTemplate.xml'],
        ];
    }
}

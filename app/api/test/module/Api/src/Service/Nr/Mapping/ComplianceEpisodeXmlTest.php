<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Mapping;

use Dvsa\Olcs\Api\Service\Nr\Mapping\ComplianceEpisodeXml;
use Olcs\XmlTools\Filter\MapXmlFile;

/**
 * Class ComplianceEpisodeXmlTest
 * @package Dvsa\OlcsTest\Api\Service\Nr\Mapping
 */
class ComplianceEpisodeXmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * loads in erru test templates from /module/Api/data/nr/ folder, tests correct data retrieval/mapping
     *
     * @dataProvider dpTemplate
     *
     * @param $template
     */
    public function testXmlMapping($template)
    {
        $domDocument = new \DOMDocument();
        $path = dirname(__DIR__) . '/../../../../../../module/Api/data/nr/' . $template;
        $domDocument->load($path);

        $sut = new ComplianceEpisodeXml(new MapXmlFile(), 'https://webgate.ec.testa.eu/erru/1.0');

        $expected = [
            'workflowId' => '20776dc3-5fe7-42d5-b554-09ad12fa25c4',
            'memberStateCode' => 'PL',
            'sentAt' => '2014-02-20T16:22:09Z',
            'notificationNumber' => '0ffefb6b-6344-4a60-9a53-4381c32f98d9',
            'originatingAuthority' => 'Driver & Vehicle Agency',
            'communityLicenceNumber' => 'UKGB/OB1234567/00000',
            'vrm' => 'aBc123',
            'transportUndertakingName' => 'TEST USER (SELF SERVICE)(12345)',
            'notificationDateTime' => '2014-02-20T16:20:12Z',
            'si' => [
                0 => [
                    'infringementDate' => '2014-02-20',
                    'siCategoryType' => 101,
                    'checkDate' => '2014-02-20',
                    'imposedErrus' => [
                        0 => [
                            'finalDecisionDate' => '2014-02-20',
                            'siPenaltyImposedType' => 101,
                            'startDate' => '2014-03-14',
                            'endDate' => '2014-09-17',
                            'executed' => 'Yes',
                        ],
                        1 => [
                            'finalDecisionDate' => '2014-06-25',
                            'siPenaltyImposedType' => 102,
                            'startDate' => '',
                            'endDate' => '',
                            'executed' => 'No',
                        ],
                    ],
                    'requestedErrus' => [
                        0 => [
                            'siPenaltyRequestedType' => 301,
                            'duration' => 12
                        ],
                        1 => [
                            'siPenaltyRequestedType' => 302,
                            'duration' => 30
                        ]
                    ]
                ],
                1 => [
                    'infringementDate' => '2014-03-21',
                    'siCategoryType' => '201',
                    'checkDate' => '2014-03-21',
                    'imposedErrus' => [
                        0 => [
                            'finalDecisionDate' => '2014-03-21',
                            'siPenaltyImposedType' => 202,
                            'startDate' => '2014-04-15',
                            'endDate' => '2014-10-18',
                            'executed' => 'No',
                        ],
                        1 => [
                            'finalDecisionDate' => '2014-07-26',
                            'siPenaltyImposedType' => 203,
                            'startDate' => '',
                            'endDate' => '',
                            'executed' => 'Yes',
                        ],
                    ],
                    'requestedErrus' => [
                        0 => [
                            'siPenaltyRequestedType' => 305,
                            'duration' => 18
                        ],
                        1 => [
                            'siPenaltyRequestedType' => 306,
                            'duration' => 24
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $sut->mapData($domDocument));
    }

    /**
     * data provider for testXmlMapping
     *
     * @return array
     */
    public function dpTemplate()
    {
        return [
            ['complianceEpisodeTemplate.xml'],
            ['complianceEpisodeTemplateNoNs.xml']
        ];
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\Mapping;

use DOMDocument;
use Dvsa\Olcs\Api\Service\Nr\Mapping\CgrResponseXml;
use Olcs\XmlTools\Filter\MapXmlFile;
use PHPUnit\Framework\TestCase;

class CgrResponseXmlTest extends TestCase
{
    /**
     * @dataProvider dpTemplate
     */
    public function testXmlMapping(string $template): void
    {
        $domDocument = new DOMDocument();
        $path = dirname(__DIR__) . '/../../../../../../module/Api/data/nr/' . $template;
        $domDocument->load($path);

        $sut = new CgrResponseXml(new MapXmlFile(), 'https://webgate.ec.testa.eu/move-hub/erru/3.4');

        $expected = [
            'version' => '3.4',
            'workflowId' => '446940dc-eb68-4462-9424-4893ce051fdb',
            'technicalId' => 'f148c54c-488f-42bc-bae1-8eb09e8f7a3f',
            'sentAt' => '2016-01-01T00:00:05Z',
            'searchedTransportManager' =>
                [
                    'transportManagerNameDetails' =>
                        [
                            'familyName' => 'Creighton-Ward',
                            'firstName' => 'Penelope',
                            'dateOfBirth' => '1939-12-24',
                            'familyNameSearchKey' => 'CRAGTANWAD',
                            'firstNameSearchKey' => 'PANALAP',
                        ],
                    'transportManagerCertificateDetails' =>
                        [
                            'certificateNumber' => 'CPC004',
                            'certificateIssueDate' => '2012-01-31',
                            'certificateIssueCountry' => 'UK',
                        ],
                ],
            'memberStateResponses' =>
                [
                    0 =>
                        [
                            'memberStateCode' => 'AT',
                            'statusCode' => 'NotFound',
                            'transportManagerDetails' => [],
                        ],
                    1 =>
                        [
                            'memberStateCode' => 'BE',
                            'statusCode' => 'NotFound',
                            'transportManagerDetails' => [],
                        ],
                    2 =>
                        [
                            'memberStateCode' => 'UK',
                            'statusCode' => 'Found',
                            'transportManagerDetails' =>
                                [
                                    'respondingAuthority' => ' UK Competent Authority ',
                                    'searchMethod' => 'CPC',
                                    'transportManagerNameDetails' =>
                                        [
                                            'familyName' => 'Creighton-Ward',
                                            'firstName' => 'Penelope',
                                            'dateOfBirth' => '1939-12-24',
                                            'placeOfBirth' => 'London',
                                        ],
                                    'transportManagerAddressDetails' =>
                                        [
                                            'address' => 'Address Line 1',
                                            'postCode' => 'W1',
                                            'city' => 'London',
                                            'country' => 'UK',
                                        ],
                                    'transportManagerCertificateDetails' =>
                                        [
                                            'certificateNumber' => 'CPC004',
                                            'certificateIssueDate' => '2012-01-31',
                                            'certificateIssueCountry' => 'UK',
                                            'certificateValidity' => 'Invalid',
                                            'fitness' =>
                                                [
                                                    'fitnessStatus' => 'Unfit',
                                                    'unfitStartDate' => '2015-12-09',
                                                    'unfitEndDate' => '2016-06-09',
                                                ],
                                        ],
                                    'transportUndertakings' =>
                                        [
                                            'totalManagedUndertakings' => '2',
                                            'totalManagedVehicles' => '18',
                                            'transportUndertaking' =>
                                                [
                                                    0 =>
                                                        [
                                                            'transportUndertakingName' => 'International Rescue',
                                                            'numberOfVehicles' => '7',
                                                            'communityLicenceNumber' => 'CL-IR-000001-001',
                                                            'communityLicenceStatus' => 'Active',
                                                            'transportUndertakingAddress' =>
                                                                [
                                                                    'address' => 'Tracy Villa',
                                                                    'postCode' => 'SP1',
                                                                    'city' => 'Tracy Island',
                                                                    'country' => 'UK',
                                                                ],
                                                        ],
                                                    1 =>
                                                        [
                                                            'transportUndertakingName' => 'New Rescue',
                                                            'numberOfVehicles' => '11',
                                                            'communityLicenceNumber' => 'CL-IR-123456-121',
                                                            'communityLicenceStatus' => 'Active',
                                                            'transportUndertakingAddress' =>
                                                                [
                                                                    'address' => 'Quarry House',
                                                                    'postCode' => 'SP1',
                                                                    'city' => 'Leeds',
                                                                    'country' => 'UK',
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ];

        $this->assertEquals($expected, $sut->mapData($domDocument));
    }

    public function dpTemplate(): array
    {
        return [
            ['checkGoodReputeResponseTemplate.xml'],
        ];
    }
}

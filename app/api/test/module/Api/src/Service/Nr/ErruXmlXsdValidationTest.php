<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr;

use Olcs\XmlTools\Xml\XmlNodeBuilder;
use PHPUnit\Framework\TestCase;

class ErruXmlXsdValidationTest extends TestCase
{
    private const XML_NS = 'https://webgate.ec.testa.eu/move-hub/erru/3.5';
    private const XSD_DIR = __DIR__ . '/../../../../../../module/Api/data/nr/xsd/3.5/';

    public function testMsiResponseXmlValidatesAgainstXsd(): void
    {
        $xmlBuilder = new XmlNodeBuilder('NotifyCheckResult_Response', self::XML_NS, []);

        $xmlData = [
            'Header' => [
                'name' => 'Header',
                'attributes' => [
                    'version' => '3.5',
                    'technicalId' => 'd0ae5498-339a-49ad-bcaa-65c1f03f6307',
                    'workflowId' => 'e933f62c-ceae-4833-b022-c4f69e2211ef',
                    'sentAt' => '2024-01-15T10:30:00Z',
                    'timeoutValue' => '2024-01-15T10:30:10Z',
                    'from' => 'UK',
                    'to' => 'EU',
                ],
            ],
            'Body' => [
                'name' => 'Body',
                'attributes' => [
                    'businessCaseId' => '0ffefb6b-6344-4a60-9a53-4381c32f',
                    'originatingAuthority' => 'Driver & Vehicle Agency',
                    'respondingAuthority' => 'Traffic Commissioner',
                    'statusCode' => 'OK',
                ],
                'nodes' => [
                    [
                        'name' => 'TransportUndertaking',
                        'attributes' => [
                            'transportUndertakingName' => 'Test Transport Ltd',
                            'communityLicenceNumber' => 'UKGB/OB1234567/00001',
                            'communityLicenceStatus' => 'Active',
                            'numberOfVehicles' => '10',
                        ],
                        'nodes' => [
                            [
                                'name' => 'TransportUndertakingAddress',
                                'attributes' => [
                                    'address' => '123 Test Street',
                                    'postCode' => 'LS1 1AA',
                                    'city' => 'Leeds',
                                    'country' => 'UK',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'PenaltiesImposed',
                        'nodes' => [
                            [
                                'name' => 'PenaltyImposed',
                                'attributes' => [
                                    'penaltyRequestedIdentifier' => '1',
                                    'authorityImposingPenalty' => 'Traffic Commissioner',
                                    'isImposed' => 'false',
                                    'penaltyTypeImposed' => '101',
                                    'reason' => 'Further sanction not required',
                                ],
                            ],
                            [
                                'name' => 'PenaltyImposed',
                                'attributes' => [
                                    'penaltyRequestedIdentifier' => '2',
                                    'authorityImposingPenalty' => 'Traffic Commissioner',
                                    'isImposed' => 'true',
                                    'penaltyTypeImposed' => '301',
                                    'startDate' => '2024-02-01',
                                    'endDate' => '2024-08-01',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $xmlBuilder->setData($xmlData);
        $xmlString = $xmlBuilder->buildTemplate();

        $this->assertXmlValidatesAgainstXsd($xmlString, self::XSD_DIR . 'NotifyCheckResult_Response.xsd');
    }

    public function testCheckGoodReputeXmlValidatesAgainstXsd(): void
    {
        $xmlBuilder = new XmlNodeBuilder('CheckGoodRepute_Request', self::XML_NS, []);

        $xmlData = [
            'Header' => [
                'name' => 'Header',
                'attributes' => [
                    'version' => '3.5',
                    'technicalId' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                    'workflowId' => 'f9e8d7c6-b5a4-3210-fedc-ba0987654321',
                    'sentAt' => '2024-01-15T10:30:00Z',
                    'timeoutValue' => '2024-01-15T10:30:10Z',
                    'from' => 'UK',
                    'to' => 'ZZ',
                ],
            ],
            'Body' => [
                'name' => 'Body',
                'attributes' => [
                    'businessCaseId' => 'b1c2d3e4-f5a6-7890-bcde-f12345678901',
                    'originatingAuthority' => 'Traffic Commissioner',
                    'requestPurpose' => 'Other',
                    'requestSource' => 'Other',
                ],
                'nodes' => [
                    [
                        'name' => 'SearchedTransportManager',
                        'nodes' => [
                            [
                                'name' => 'TransportManagerNameDetails',
                                'attributes' => [
                                    'familyName' => 'Smith',
                                    'firstName' => 'John',
                                    'dateOfBirth' => '1980-05-15',
                                    'placeOfBirth' => 'London',
                                ],
                            ],
                            [
                                'name' => 'TransportManagerCertificateDetails',
                                'attributes' => [
                                    'certificateNumber' => 'CPC001',
                                    'certificateIssueDate' => '2015-06-01',
                                    'certificateIssueCountry' => 'UK',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $xmlBuilder->setData($xmlData);
        $xmlString = $xmlBuilder->buildTemplate();

        $this->assertXmlValidatesAgainstXsd($xmlString, self::XSD_DIR . 'CheckGoodRepute_Request.xsd');
    }

    private function assertXmlValidatesAgainstXsd(string $xml, string $xsdPath): void
    {
        $this->assertFileExists($xsdPath, "XSD file not found: $xsdPath");

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        libxml_use_internal_errors(true);
        $isValid = $dom->schemaValidate($xsdPath);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = sprintf('Line %d: %s', $error->line, trim($error->message));
        }

        $this->assertTrue(
            $isValid,
            "XML failed XSD validation:\n" . implode("\n", $errorMessages) . "\n\nXML:\n" . $xml
        );
    }
}

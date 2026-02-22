<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\TmReputeCheck;

use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck\Generator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class GeneratorTest extends MockeryTestCase
{
    private Generator $sut;
    private m\MockInterface|PhpRenderer $viewRenderer;

    protected function setUp(): void
    {
        $this->viewRenderer = m::mock(PhpRenderer::class);

        $abstractGeneratorServices = m::mock(AbstractGeneratorServices::class);
        $abstractGeneratorServices->expects('getRenderer')
            ->withNoArgs()
            ->andReturn($this->viewRenderer);

        $this->sut = new Generator($abstractGeneratorServices);
    }

    /**
     * Tests the snapshot generation process. Currently tests the XML data is translated into what the snapshot
     * code expects, next step is to also validate output
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $input, mixed $expectedConfig, mixed $expectedOutput): void
    {
        $this->viewRenderer->shouldReceive('render')
            ->once()
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(
                function (ViewModel $viewModel) use ($expectedConfig, $expectedOutput) {
                    $this->assertEquals($expectedConfig, $viewModel->getVariables());
                    $this->assertEquals('layout/check-repute-response', $viewModel->getTemplate());
                    $this->assertTrue($viewModel->terminate());

                    return $expectedOutput;
                }
            );

        ;
        $this->assertEquals($expectedOutput, $this->sut->generate($input));
    }

    public static function dpGenerate(): array
    {
        return [
            [self::getExpectedInput(), self::getExpectedConfig(), self::getExpectedOutput()],
        ];
    }

    private static function getExpectedInput(): array
    {
        return [
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
    }

    private static function getExpectedConfig(): array
    {
        return [
            'tmName' => 'Penelope Creighton-Ward',
            'searchDetails' =>
                [
                    'Date and time of check' => '2016-01-01T00:00:05Z',
                    'Workflow ID' => '446940dc-eb68-4462-9424-4893ce051fdb',
                    'Technical ID' => 'f148c54c-488f-42bc-bae1-8eb09e8f7a3f',
                    'Last name' => 'Creighton-Ward',
                    'First name' => 'Penelope',
                    'Date of birth' => '1939-12-24',
                    'Family name search key' => 'CRAGTANWAD',
                    'First name search key' => 'PANALAP',
                    'Certificate number' => 'CPC004',
                    'Certificate issue date' => '2012-01-31',
                    'Certificate issue country' => 'UK',
                ],
            'foundCountries' =>
                [
                    0 =>
                        [
                            'name' => 'UK',
                            'details' =>
                                [
                                    'Responding authority' => ' UK Competent Authority ',
                                    'Last name' => 'Creighton-Ward',
                                    'First name' => 'Penelope',
                                    'Date of birth' => 'Penelope',
                                    'Place of birth' => 'London',
                                    'Address' => 'Address Line 1',
                                    'Postcode' => 'W1',
                                    'City' => 'London',
                                    'Country' => 'UK',
                                    'Certificate number' => 'CPC004',
                                    'Certificate issue date' => '2012-01-31',
                                    'Certificate issue country' => 'UK',
                                    'Certificate validity' => 'Invalid',
                                    'Fitness status' => 'Unfit',
                                    'Unfit start date' => '2015-12-09',
                                    'Unfit end date' => '2016-06-09',
                                    'Total managed undertakings' => '2',
                                    'Total managed vehicles' => '18',
                                ],
                            'undertakings' =>
                                [
                                    0 =>
                                        [
                                            'Transport undertaking name' => 'International Rescue',
                                            'Number of vehicles' => '7',
                                            'Community licence number' => 'CL-IR-000001-001',
                                            'Community licence status' => 'Active',
                                            'Transport undertaking address' => 'Tracy Villa',
                                            'Transport undertaking postcode' => 'SP1',
                                            'Transport undertaking city' => 'Tracy Island',
                                            'Transport undertaking country' => 'UK',
                                        ],
                                    1 =>
                                        [
                                            'Transport undertaking name' => 'New Rescue',
                                            'Number of vehicles' => '11',
                                            'Community licence number' => 'CL-IR-123456-121',
                                            'Community licence status' => 'Active',
                                            'Transport undertaking address' => 'Quarry House',
                                            'Transport undertaking postcode' => 'SP1',
                                            'Transport undertaking city' => 'Leeds',
                                            'Transport undertaking country' => 'UK',
                                        ],
                                ],
                        ],
                ],
            'notFoundCountries' => 'AT, BE',
        ];
    }

    private static function getExpectedOutput(): string
    {
        return '<html>';
    }
}

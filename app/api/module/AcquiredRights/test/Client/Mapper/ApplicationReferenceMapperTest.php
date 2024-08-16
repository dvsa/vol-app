<?php

namespace Dvsa\Olcs\AcquiredRights\Client;

use Dvsa\Olcs\AcquiredRights\Client\Mapper\ApplicationReferenceMapper;
use Dvsa\Olcs\AcquiredRights\Exception\MapperParseException;
use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ApplicationReferenceMapperTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ApplicationReferenceMapper();
    }

    /**
     * @test
     * @dataProvider dataProviderResponseDataAndExceptionMap
     */
    public function createFromResponseArrayValidOrThrowsAppropriateExceptions(array $data, string $exceptionMessage = null)
    {
        if (!is_null($exceptionMessage)) {
            $this->expectException(MapperParseException::class);
            $this->expectExceptionMessage($exceptionMessage);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut::createFromResponseArray($data);
    }

    public function dataProviderResponseDataAndExceptionMap(): array
    {
        return [
            'Valid' => [
                $this->generateData(),
                null
            ],
            'ID is not defined' => [
                $this->generateData([], 'id'),
                'Id must not be empty().'
            ],
            'ID is empty string' => [
                $this->generateData([
                    'id' => '',
                ]),
                'Id must not be empty().'
            ],
            'ID does not match regex (UUIDv4)' => [
                $this->generateData([
                    'id' => 'ljnsdgnjlsdgljnsdgljknsdg',
                ]),
                'Id is not a valid UUIDv4.'
            ],
            'Reference is not defined' => [
                $this->generateData([], 'reference'),
                'Reference must not be empty().'
            ],
            'Reference is empty string' => [
                $this->generateData([
                    'reference' => '',
                ]),
                'Reference must not be empty().'
            ],
            'Reference does not match regex (7 Character AlphaNum String)' => [
                $this->generateData([
                    'reference' => 'ABC123',
                ]),
                'Reference is not valid.'
            ],
            'Status is not defined' => [
                $this->generateData([], 'status'),
                'Status must not be empty().'
            ],
            'Status is empty string' => [
                $this->generateData([
                    'status' => '',
                ]),
                'Status must not be empty().'
            ],
            'Status is not in allowed values' => [
                $this->generateData([
                    'status' => 'Some non-existent status',
                ]),
                'Application Status is not valid.'
            ],
            'SubmittedOn is not defined' => [
                $this->generateData([], 'submittedOn'),
                'Submitted On must not be empty().'
            ],
            'SubmittedOn is empty string' => [
                $this->generateData([
                    'submittedOn' => '',
                ]),
                'Submitted On must not be empty().'
            ],
            'SubmittedOn does not match timestamp format' => [
                $this->generateData([
                    'submittedOn' => '10 Jan 1990',
                ]),
                'Submitted On could not parse into DateTime from format'
            ],
            'DateOfBirh is not defined' => [
                $this->generateData([], 'dateOfBirth'),
                'Date of Birth must not be empty().'
            ],
            'Date of Birth is empty string' => [
                $this->generateData([
                    'dateOfBirth' => '',
                ]),
                'Date of Birth must not be empty().'
            ],
            'Date of Birth does not match timestamp format' => [
                $this->generateData([
                    'dateOfBirth' => '10 Jan 1990',
                ]),
                'Date of Birth could not parse into DateTime from format'
            ],
            'Status Update On does not match timestamp format' => [
                $this->generateData([
                    'statusUpdateOn' => '10 Jan 1990',
                ]),
                'Status Update On could not parse into DateTime from format'
            ],
        ];
    }

    protected function generateData(array $override = [], string $unsetKey = null): array
    {
        $result = array_merge([
            'id' => '6fcf9551-ade4-4b48-b078-6db59559a182',
            'reference' => 'ABC1234',
            'status' => ApplicationReference::APPLICATION_STATUS_SUBMITTED,
            'submittedOn' => 'Mon, 13 Dec 2021 10:00:41 GMT',
            'dateOfBirth' => '2011-01-01T00:00:00.000Z',
            'statusUpdateOn' => 'Mon, 13 Dec 2021 10:00:41 GMT'
        ], $override);

        if ($unsetKey) {
            unset($result[$unsetKey]);
        }

        return $result;
    }
}

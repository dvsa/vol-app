<?php

declare(strict_types=1);

namespace Dvsa\Olcs\AcquiredRights\Client;

use Dvsa\Olcs\AcquiredRights\Client\Mapper\ApplicationReferenceMapper;
use Dvsa\Olcs\AcquiredRights\Exception\MapperParseException;
use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ApplicationReferenceMapperTest extends MockeryTestCase
{
    protected ApplicationReferenceMapper $sut;

    public function setUp(): void
    {
        $this->sut = new ApplicationReferenceMapper();
    }

    #[DataProvider('dataProviderResponseDataAndExceptionMap')]
    #[Test]
    public function createFromResponseArrayValidOrThrowsAppropriateExceptions(array $data, string $exceptionMessage = null): void
    {
        if (!is_null($exceptionMessage)) {
            $this->expectException(MapperParseException::class);
            $this->expectExceptionMessage($exceptionMessage);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->sut::createFromResponseArray($data);
    }

    public static function dataProviderResponseDataAndExceptionMap(): array
    {
        return [
            'Valid' => [
                self::generateData(),
                null
            ],
            'ID is not defined' => [
                self::generateData([], 'id'),
                'Id must not be empty().'
            ],
            'ID is empty string' => [
                self::generateData([
                    'id' => '',
                ]),
                'Id must not be empty().'
            ],
            'ID does not match regex (UUIDv4)' => [
                self::generateData([
                    'id' => 'ljnsdgnjlsdgljnsdgljknsdg',
                ]),
                'Id is not a valid UUIDv4.'
            ],
            'Reference is not defined' => [
                self::generateData([], 'reference'),
                'Reference must not be empty().'
            ],
            'Reference is empty string' => [
                self::generateData([
                    'reference' => '',
                ]),
                'Reference must not be empty().'
            ],
            'Reference does not match regex (7 Character AlphaNum String)' => [
                self::generateData([
                    'reference' => 'ABC123',
                ]),
                'Reference is not valid.'
            ],
            'Status is not defined' => [
                self::generateData([], 'status'),
                'Status must not be empty().'
            ],
            'Status is empty string' => [
                self::generateData([
                    'status' => '',
                ]),
                'Status must not be empty().'
            ],
            'Status is not in allowed values' => [
                self::generateData([
                    'status' => 'Some non-existent status',
                ]),
                'Application Status is not valid.'
            ],
            'SubmittedOn is not defined' => [
                self::generateData([], 'submittedOn'),
                'Submitted On must not be empty().'
            ],
            'SubmittedOn is empty string' => [
                self::generateData([
                    'submittedOn' => '',
                ]),
                'Submitted On must not be empty().'
            ],
            'SubmittedOn does not match timestamp format' => [
                self::generateData([
                    'submittedOn' => '10 Jan 1990',
                ]),
                'Submitted On could not parse into DateTime from format'
            ],
            'DateOfBirh is not defined' => [
                self::generateData([], 'dateOfBirth'),
                'Date of Birth must not be empty().'
            ],
            'Date of Birth is empty string' => [
                self::generateData([
                    'dateOfBirth' => '',
                ]),
                'Date of Birth must not be empty().'
            ],
            'Date of Birth does not match timestamp format' => [
                self::generateData([
                    'dateOfBirth' => '10 Jan 1990',
                ]),
                'Date of Birth could not parse into DateTime from format'
            ],
            'Status Update On does not match timestamp format' => [
                self::generateData([
                    'statusUpdateOn' => '10 Jan 1990',
                ]),
                'Status Update On could not parse into DateTime from format'
            ],
        ];
    }

    private static function generateData(array $override = [], string $unsetKey = null): array
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

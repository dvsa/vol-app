<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Ebsr;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as Entity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * EbsrSubmission Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class EbsrSubmissionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * tests entity creation
     */
    public function testCreate(): void
    {
        $document = m::mock(DocumentEntity::class);
        $organisation = m::mock(OrganisationEntity::class);

        //setting ids here is a way to make the objects unique
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionStatus->setId('some id');
        $ebsrSubmissionType = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionType->setId('some other id');

        $entity = new Entity($organisation, $ebsrSubmissionStatus, $ebsrSubmissionType, $document);

        $this->assertEquals($organisation, $entity->getOrganisation());
        $this->assertEquals($document, $entity->getDocument());
        $this->assertEquals($ebsrSubmissionStatus, $entity->getEbsrSubmissionStatus());
        $this->assertEquals($ebsrSubmissionType, $entity->getEbsrSubmissionType());
    }

    /**
     * tests submit
     */
    public function testSubmit(): void
    {
        //setting ids here is a way to make the objects unique
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionStatus->setId('some id');
        $ebsrSubmissionType = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionType->setId('some other id');

        $entity = $this->instantiate(Entity::class);

        $entity->submit($ebsrSubmissionStatus, $ebsrSubmissionType);

        $this->assertEquals($ebsrSubmissionStatus, $entity->getEbsrSubmissionStatus());
        $this->assertEquals($ebsrSubmissionType, $entity->getEbsrSubmissionType());
        $this->assertInstanceOf(\DateTime::class, $entity->getSubmittedDate());
    }

    /**
     * tests beginValidating
     */
    public function testBeginValidating(): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();

        $previousEbsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $previousEbsrSubmissionStatus->setId(Entity::SUBMITTED_STATUS);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($previousEbsrSubmissionStatus);

        $entity->beginValidating($ebsrSubmissionStatus);

        $this->assertEquals($ebsrSubmissionStatus, $entity->getEbsrSubmissionStatus());
        $this->assertInstanceOf(\DateTime::class, $entity->getValidationStart());
    }

    /**
     * tests beginValidating throws an exception for incorrect statuses
     *
     *
     * @param string $previousStatus
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('beginValidatingProvider')]
    public function testBeginValidatingThrowsException(mixed $previousStatus): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();

        $previousEbsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $previousEbsrSubmissionStatus->setId($previousStatus);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($previousEbsrSubmissionStatus);

        $entity->beginValidating($ebsrSubmissionStatus);
    }

    /**
     * Date provider for testBeginValidatingThrowsException
     *
     * @return array
     */
    public static function beginValidatingProvider(): array
    {
        return [
            [Entity::UPLOADED_STATUS],
            [Entity::VALIDATING_STATUS],
            [Entity::PROCESSING_STATUS],
            [Entity::PROCESSED_STATUS],
            [Entity::FAILED_STATUS]
        ];
    }

    /**
     * tests finishValidating
     */
    public function testFinishValidatingWithFailure(): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionStatus->setId(Entity::FAILED_STATUS);

        $ebsrSubmissionResult = ['submission result'];
        $encodedSubmissionResult = json_encode($ebsrSubmissionResult);

        $entity = $this->instantiate(Entity::class);

        $entity->finishValidating($ebsrSubmissionStatus, $ebsrSubmissionResult);

        $this->assertEquals($ebsrSubmissionStatus, $entity->getEbsrSubmissionStatus());
        $this->assertEquals($encodedSubmissionResult, $entity->getEbsrSubmissionResult());
        $this->assertInstanceOf(\DateTime::class, $entity->getValidationEnd());
        $this->assertNull($entity->getProcessStart());
    }

    /**
     * tests finishValidating
     */
    public function testFinishValidatingNoFailure(): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionResult = ['submission result'];
        $encodedSubmissionResult = json_encode($ebsrSubmissionResult);

        $entity = $this->instantiate(Entity::class);

        $entity->finishValidating($ebsrSubmissionStatus, $ebsrSubmissionResult);

        $this->assertEquals($ebsrSubmissionStatus, $entity->getEbsrSubmissionStatus());
        $this->assertEquals($encodedSubmissionResult, $entity->getEbsrSubmissionResult());
        $this->assertInstanceOf(\DateTime::class, $entity->getValidationEnd());
        $this->assertEquals($entity->getProcessStart(), $entity->getValidationEnd());
    }

    /**
     * tests finishProcessing
     */
    public function testFinishProcessing(): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();

        $entity = $this->instantiate(Entity::class);

        $entity->finishProcessing($ebsrSubmissionStatus);

        $this->assertEquals($ebsrSubmissionStatus, $entity->getEbsrSubmissionStatus());
        $this->assertInstanceOf(\DateTime::class, $entity->getProcessEnd());
    }

    /**
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isFailureProvider')]
    public function testIsFailure(mixed $submissionStatusString, mixed $expectedResult): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->once()->andReturn($submissionStatusString);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);

        $this->assertEquals($expectedResult, $entity->isFailure());
    }

    /**
     * Date provider for isFailure
     *
     * @return array
     */
    public static function isFailureProvider(): array
    {
        return [
            [Entity::UPLOADED_STATUS, false],
            [Entity::SUBMITTED_STATUS, false],
            [Entity::VALIDATING_STATUS, false],
            [Entity::PROCESSING_STATUS, false],
            [Entity::PROCESSED_STATUS, false],
            [Entity::FAILED_STATUS, true]
        ];
    }

    /**
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isSubmittedProvider')]
    public function testIsSubmitted(mixed $submissionStatusString, mixed $expectedResult): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->once()->andReturn($submissionStatusString);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);

        $this->assertEquals($expectedResult, $entity->isSubmitted());
    }

    /**
     * Date provider for isSubmitted
     *
     * @return array
     */
    public static function isSubmittedProvider(): array
    {
        return [
            [Entity::UPLOADED_STATUS, false],
            [Entity::SUBMITTED_STATUS, true],
            [Entity::VALIDATING_STATUS, false],
            [Entity::PROCESSING_STATUS, false],
            [Entity::PROCESSED_STATUS, false],
            [Entity::FAILED_STATUS, false]
        ];
    }

    /**
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isSuccessProvider')]
    public function testIsSuccess(mixed $submissionStatusString, mixed $expectedResult): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->once()->andReturn($submissionStatusString);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);

        $this->assertEquals($expectedResult, $entity->isSuccess());
    }

    /**
     * Date provider for isSuccess
     *
     * @return array
     */
    public static function isSuccessProvider(): array
    {
        return [
            [Entity::UPLOADED_STATUS, false],
            [Entity::SUBMITTED_STATUS, false],
            [Entity::VALIDATING_STATUS, false],
            [Entity::PROCESSING_STATUS, false],
            [Entity::PROCESSED_STATUS, true],
            [Entity::FAILED_STATUS, false]
        ];
    }

    /**
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isBeingProcessedProvider')]
    public function testIsBeingProcessed(mixed $submissionStatusString, mixed $expectedResult): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->once()->andReturn($submissionStatusString);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);

        $this->assertEquals($expectedResult, $entity->isBeingProcessed());
    }

    /**
     * Date provider for isBeingProcessed
     *
     * @return array
     */
    public static function isBeingProcessedProvider(): array
    {
        return [
            [Entity::UPLOADED_STATUS, false],
            [Entity::SUBMITTED_STATUS, true],
            [Entity::VALIDATING_STATUS, true],
            [Entity::PROCESSING_STATUS, true],
            [Entity::PROCESSED_STATUS, false],
            [Entity::FAILED_STATUS, false]
        ];
    }

    /**
     * tests get errors returns empty array when the submission isn't a failure
     *
     *
     * @param $submissionStatusString
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getErrorsWithNoFailureProvider')]
    public function testGetErrorsWithNoFailure(mixed $submissionStatusString): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->once()->andReturn($submissionStatusString);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);

        $this->assertEquals([], $entity->getErrors());
    }

    /**
     * Date provider for isBeingProcessed
     *
     * @return array
     */
    public static function getErrorsWithNoFailureProvider(): array
    {
        return [
            [Entity::UPLOADED_STATUS],
            [Entity::SUBMITTED_STATUS],
            [Entity::VALIDATING_STATUS],
            [Entity::PROCESSING_STATUS],
            [Entity::PROCESSED_STATUS]
        ];
    }

    /**
     * tests getErrors
     */
    public function testGetErrors(): void
    {
        $errorArray = [
            0 => 'error1',
            1 => 'error2'
        ];

        $errors = [
            'errors' => $errorArray
        ];

        $entity = $this->instantiate(Entity::class);
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->once()->andReturn(Entity::FAILED_STATUS);

        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);
        $entity->setEbsrSubmissionResult(json_encode($errors));

        $this->assertEquals($errorArray, $entity->getErrors());
    }

    /**
     * tests getErrors when we have legacy data
     */
    public function testGetErrorsWithLegacyData(): void
    {
        $entity = $this->instantiate(Entity::class);
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->once()->andReturn(Entity::FAILED_STATUS);

        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);
        $entity->setEbsrSubmissionResult('error as a string');

        $this->assertEquals([], $entity->getErrors());
    }

    /**
     * tests calculated bundle values
     */
    public function testGetCalculatedBundleValues(): void
    {
        $ebsrSubmissionStatus = m::mock(RefData::class);
        $ebsrSubmissionStatus->shouldReceive('getId')->times(4)->andReturn(Entity::PROCESSED_STATUS);

        $entity = $this->instantiate(Entity::class);
        $entity->setEbsrSubmissionStatus($ebsrSubmissionStatus);

        $result = $entity->getCalculatedBundleValues();

        $this->assertEquals(false, $result['isBeingProcessed']);
        $this->assertEquals(false, $result['isFailure']);
        $this->assertEquals(true, $result['isSuccess']);
        $this->assertEquals([], $result['errors']);
    }

    /**
     *
     * @param string $status
     * @param bool $result
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isDataRefreshProvider')]
    public function testIsDataRefresh(mixed $status, mixed $result): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $submissionType = new RefData($status);
        $entity->setEbsrSubmissionType($submissionType);
        $this->assertEquals($result, $entity->isDataRefresh());
    }

    /**
     * Data provider for testIsDataRefresh
     *
     * @return array
     */
    public static function isDataRefreshProvider(): array
    {
        return [
            [Entity::DATA_REFRESH_SUBMISSION_TYPE, true],
            [Entity::NEW_SUBMISSION_TYPE, false]
        ];
    }

    /**
     * Tests getRelatedOrganisation (used by validators)
     */
    public function testGetRelatedOrganisation(): void
    {
        $organisation = m::mock(OrganisationEntity::class);
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setOrganisation($organisation);

        $this->assertEquals($organisation, $entity->getRelatedOrganisation());
    }
}

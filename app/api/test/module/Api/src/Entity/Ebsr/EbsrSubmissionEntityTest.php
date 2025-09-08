<?php

namespace Dvsa\OlcsTest\Api\Entity\Ebsr;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as Entity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Laminas\Json\Json as LaminasJson;

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
    public function testCreate()
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
    public function testSubmit()
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
    public function testBeginValidating()
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
     * @dataProvider beginValidatingProvider
     *
     * @param string $previousStatus
     */
    public function testBeginValidatingThrowsException($previousStatus)
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
    public function beginValidatingProvider()
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
    public function testFinishValidatingWithFailure()
    {
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionStatus->setId(Entity::FAILED_STATUS);

        $ebsrSubmissionResult = ['submission result'];
        $encodedSubmissionResult = LaminasJson::encode($ebsrSubmissionResult);

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
    public function testFinishValidatingNoFailure()
    {
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();
        $ebsrSubmissionResult = ['submission result'];
        $encodedSubmissionResult = LaminasJson::encode($ebsrSubmissionResult);

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
    public function testFinishProcessing()
    {
        $ebsrSubmissionStatus = m::mock(RefData::class)->makePartial();

        $entity = $this->instantiate(Entity::class);

        $entity->finishProcessing($ebsrSubmissionStatus);

        $this->assertEquals($ebsrSubmissionStatus, $entity->getEbsrSubmissionStatus());
        $this->assertInstanceOf(\DateTime::class, $entity->getProcessEnd());
    }

    /**
     * @dataProvider isFailureProvider
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    public function testIsFailure($submissionStatusString, $expectedResult)
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
    public function isFailureProvider()
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
     * @dataProvider isSubmittedProvider
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    public function testIsSubmitted($submissionStatusString, $expectedResult)
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
    public function isSubmittedProvider()
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
     * @dataProvider isSuccessProvider
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    public function testIsSuccess($submissionStatusString, $expectedResult)
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
    public function isSuccessProvider()
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
     * @dataProvider isBeingProcessedProvider
     *
     * @param $submissionStatusString
     * @param $expectedResult
     */
    public function testIsBeingProcessed($submissionStatusString, $expectedResult)
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
    public function isBeingProcessedProvider()
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
     * @dataProvider getErrorsWithNoFailureProvider
     *
     * @param $submissionStatusString
     */
    public function testGetErrorsWithNoFailure($submissionStatusString)
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
    public function getErrorsWithNoFailureProvider()
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
    public function testGetErrors()
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
        $entity->setEbsrSubmissionResult(LaminasJson::encode($errors));

        $this->assertEquals($errorArray, $entity->getErrors());
    }

    /**
     * tests getErrors when we have legacy data
     */
    public function testGetErrorsWithLegacyData()
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
    public function testGetCalculatedBundleValues()
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
     * @dataProvider isDataRefreshProvider
     *
     * @param string $status
     * @param bool $result
     */
    public function testIsDataRefresh($status, $result)
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
    public function isDataRefreshProvider()
    {
        return [
            [Entity::DATA_REFRESH_SUBMISSION_TYPE, true],
            [Entity::NEW_SUBMISSION_TYPE, false]
        ];
    }

    /**
     * Tests getRelatedOrganisation (used by validators)
     */
    public function testGetRelatedOrganisation()
    {
        $organisation = m::mock(OrganisationEntity::class);
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setOrganisation($organisation);

        $this->assertEquals($organisation, $entity->getRelatedOrganisation());
    }
}

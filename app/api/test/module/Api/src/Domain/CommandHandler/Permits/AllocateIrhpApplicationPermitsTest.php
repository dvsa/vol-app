<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\AllocateIrhpApplicationPermits;
use Dvsa\Olcs\Api\Domain\Command\Permits\AllocateCandidatePermits;
use Dvsa\Olcs\Api\Domain\Command\Permits\AllocateIrhpApplicationPermits as Cmd;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtAspgIssued;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Service\Permits\Allocate\BilateralCriteria;
use Dvsa\Olcs\Api\Service\Permits\Allocate\BilateralCriteriaFactory;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteria;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteriaFactory;
use Dvsa\Olcs\Api\Service\Permits\Allocate\IrhpPermitAllocator;
use Mockery as m;
use RuntimeException;

class AllocateIrhpApplicationPermitsTest extends AbstractCommandHandlerTestCase
{
    private $irhpApplicationId;

    private $command;

    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsAllocateEmissionsStandardCriteriaFactory' => m::mock(EmissionsStandardCriteriaFactory::class),
            'PermitsAllocateBilateralCriteriaFactory' => m::mock(BilateralCriteriaFactory::class),
            'PermitsAllocateIrhpPermitAllocator' => m::mock(IrhpPermitAllocator::class),
        ];

        $this->sut = new AllocateIrhpApplicationPermits();

        $this->irhpApplicationId = 110;

        $this->command = m::mock(Cmd::class);
        $this->command->shouldReceive('getId')
            ->andReturn($this->irhpApplicationId);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpInterface::STATUS_VALID
        ];

        parent::initReferences();
    }

    public function testHandleCommandStandard(): void
    {
        $irhpPermitApplication1PermitsRequired = 3;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('countPermitsRequired')
            ->andReturn($irhpPermitApplication1PermitsRequired);

        $irhpPermitApplication2PermitsRequired = 5;
        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('countPermitsRequired')
            ->andReturn($irhpPermitApplication2PermitsRequired);

        $irhpPermitApplication3PermitsRequired = 0;
        $irhpPermitApplication3 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication3->shouldReceive('countPermitsRequired')
            ->andReturn($irhpPermitApplication3PermitsRequired);

        $irhpPermitApplications = new ArrayCollection([$irhpPermitApplication1, $irhpPermitApplication2, $irhpPermitApplication3]);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturnNull();
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_STANDARD);
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('proceedToValid')
            ->with($this->refData[IrhpInterface::STATUS_VALID])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication1, null, null)
            ->times($irhpPermitApplication1PermitsRequired);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication2, null, null)
            ->times($irhpPermitApplication2PermitsRequired);

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->irhpApplicationId,
            $result->getId('irhpApplication')
        );
    }

    public function testHandleCommandStandardWithExpiry(): void
    {
        $expiryDate = m::mock(DateTime::class);

        $irhpPermitApplication1PermitsRequired = 10;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('countPermitsRequired')
            ->andReturn($irhpPermitApplication1PermitsRequired);
        $irhpPermitApplication1->shouldReceive('generateExpiryDate')
            ->withNoArgs()
            ->andReturn($expiryDate);

        $irhpPermitApplications = new ArrayCollection([$irhpPermitApplication1]);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturnNull();
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_STANDARD_WITH_EXPIRY);
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('proceedToValid')
            ->with($this->refData[IrhpInterface::STATUS_VALID])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->once()
            ->andReturn($irhpApplication);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication1, null, $expiryDate)
            ->times($irhpPermitApplication1PermitsRequired);

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->irhpApplicationId,
            $result->getId('irhpApplication')
        );
    }

    public function testHandleCommandEmissionsCategories(): void
    {
        $irhpPermitApplication1RequiredEuro5 = 0;
        $irhpPermitApplication1RequiredEuro6 = 3;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getRequiredEuro5')
            ->andReturn($irhpPermitApplication1RequiredEuro5);
        $irhpPermitApplication1->shouldReceive('getRequiredEuro6')
            ->andReturn($irhpPermitApplication1RequiredEuro6);

        $irhpPermitApplication2RequiredEuro5 = 7;
        $irhpPermitApplication2RequiredEuro6 = 8;
        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getRequiredEuro5')
            ->andReturn($irhpPermitApplication2RequiredEuro5);
        $irhpPermitApplication2->shouldReceive('getRequiredEuro6')
            ->andReturn($irhpPermitApplication2RequiredEuro6);

        $irhpPermitApplication3RequiredEuro5 = 5;
        $irhpPermitApplication3RequiredEuro6 = 0;
        $irhpPermitApplication3 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication3->shouldReceive('getRequiredEuro5')
            ->andReturn($irhpPermitApplication3RequiredEuro5);
        $irhpPermitApplication3->shouldReceive('getRequiredEuro6')
            ->andReturn($irhpPermitApplication3RequiredEuro6);

        $irhpPermitApplications = new ArrayCollection([$irhpPermitApplication1, $irhpPermitApplication2, $irhpPermitApplication3]);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturnNull();
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_EMISSIONS_CATEGORIES);
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('proceedToValid')
            ->with($this->refData[IrhpInterface::STATUS_VALID])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplication);

        $euro5EmissionsStandardCriteria = m::mock(EmissionsStandardCriteria::class);
        $this->mockedSmServices['PermitsAllocateEmissionsStandardCriteriaFactory']->shouldReceive('create')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5EmissionsStandardCriteria);

        $euro6EmissionsStandardCriteria = m::mock(EmissionsStandardCriteria::class);
        $this->mockedSmServices['PermitsAllocateEmissionsStandardCriteriaFactory']->shouldReceive('create')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6EmissionsStandardCriteria);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication1, $euro6EmissionsStandardCriteria, null)
            ->times($irhpPermitApplication1RequiredEuro6);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication2, $euro5EmissionsStandardCriteria, null)
            ->times($irhpPermitApplication2RequiredEuro5);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication2, $euro6EmissionsStandardCriteria, null)
            ->times($irhpPermitApplication2RequiredEuro6);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication3, $euro5EmissionsStandardCriteria, null)
            ->times($irhpPermitApplication3RequiredEuro5);

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->irhpApplicationId,
            $result->getId('irhpApplication')
        );
    }

    public function testHandleCommandBilateral(): void
    {
        $irhpPermitApplication1StandardRequired = 2;
        $irhpPermitApplication1CabotageRequired = 4;
        $irhpPermitApplication1FilteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => $irhpPermitApplication1StandardRequired,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => $irhpPermitApplication1CabotageRequired,
        ];
        $irhpPermitApplication1PermitUsage = RefData::JOURNEY_SINGLE;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getFilteredBilateralRequired')
            ->andReturn($irhpPermitApplication1FilteredBilateralRequired);
        $irhpPermitApplication1->shouldReceive('getBilateralPermitUsageSelection')
            ->andReturn($irhpPermitApplication1PermitUsage);

        $irhpPermitApplication2StandardRequired = 5;
        $irhpPermitApplication2FilteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => $irhpPermitApplication2StandardRequired,
        ];
        $irhpPermitApplication2PermitUsage = RefData::JOURNEY_MULTIPLE;
        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getFilteredBilateralRequired')
            ->andReturn($irhpPermitApplication2FilteredBilateralRequired);
        $irhpPermitApplication2->shouldReceive('getBilateralPermitUsageSelection')
            ->andReturn($irhpPermitApplication2PermitUsage);

        $irhpPermitApplication3MoroccoRequired = 10;
        $irhpPermitApplication3FilteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $irhpPermitApplication3MoroccoRequired,
        ];
        $irhpPermitApplication3 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication3->shouldReceive('getFilteredBilateralRequired')
            ->andReturn($irhpPermitApplication3FilteredBilateralRequired);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitCategory->getId')
            ->withNoArgs()
            ->andReturn(RefData::PERMIT_CAT_STANDARD_MULTIPLE_15);

        $irhpPermitApplication4MoroccoRequired = 13;
        $irhpPermitApplication4FilteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $irhpPermitApplication4MoroccoRequired,
        ];
        $irhpPermitApplication4ExpiryDate = m::mock(DateTime::class);
        $irhpPermitApplication4 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication4->shouldReceive('getFilteredBilateralRequired')
            ->andReturn($irhpPermitApplication4FilteredBilateralRequired);
        $irhpPermitApplication4->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitCategory->getId')
            ->withNoArgs()
            ->andReturn(RefData::PERMIT_CAT_STANDARD_SINGLE);
        $irhpPermitApplication4->shouldReceive('generateExpiryDate')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication4ExpiryDate);

        $irhpPermitApplication5MoroccoRequired = 19;
        $irhpPermitApplication5FilteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $irhpPermitApplication5MoroccoRequired,
        ];
        $irhpPermitApplication5ExpiryDate = m::mock(DateTime::class);
        $irhpPermitApplication5 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication5->shouldReceive('getFilteredBilateralRequired')
            ->andReturn($irhpPermitApplication5FilteredBilateralRequired);
        $irhpPermitApplication5->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitCategory->getId')
            ->withNoArgs()
            ->andReturn(RefData::PERMIT_CAT_EMPTY_ENTRY);
        $irhpPermitApplication5->shouldReceive('generateExpiryDate')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication5ExpiryDate);

        $irhpPermitApplication6MoroccoRequired = 16;
        $irhpPermitApplication6FilteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $irhpPermitApplication6MoroccoRequired,
        ];
        $irhpPermitApplication6ExpiryDate = m::mock(DateTime::class);
        $irhpPermitApplication6 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication6->shouldReceive('getFilteredBilateralRequired')
            ->andReturn($irhpPermitApplication6FilteredBilateralRequired);
        $irhpPermitApplication6->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitCategory->getId')
            ->withNoArgs()
            ->andReturn(RefData::PERMIT_CAT_HORS_CONTINGENT);
        $irhpPermitApplication6->shouldReceive('generateExpiryDate')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication6ExpiryDate);

        $irhpPermitApplications = new ArrayCollection(
            [
                $irhpPermitApplication1,
                $irhpPermitApplication2,
                $irhpPermitApplication3,
                $irhpPermitApplication4,
                $irhpPermitApplication5,
                $irhpPermitApplication6
            ]
        );

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturnNull();
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_BILATERAL);
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('proceedToValid')
            ->with($this->refData[IrhpInterface::STATUS_VALID])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplication);

        $standardSingleCriteria = m::mock(BilateralCriteria::class);
        $this->mockedSmServices['PermitsAllocateBilateralCriteriaFactory']->shouldReceive('create')
            ->with(IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED, RefData::JOURNEY_SINGLE)
            ->andReturn($standardSingleCriteria);

        $cabotageSingleCriteria = m::mock(BilateralCriteria::class);
        $this->mockedSmServices['PermitsAllocateBilateralCriteriaFactory']->shouldReceive('create')
            ->with(IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED, RefData::JOURNEY_SINGLE)
            ->andReturn($cabotageSingleCriteria);

        $standardMultipleCriteria = m::mock(BilateralCriteria::class);
        $this->mockedSmServices['PermitsAllocateBilateralCriteriaFactory']->shouldReceive('create')
            ->with(IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED, RefData::JOURNEY_MULTIPLE)
            ->andReturn($standardMultipleCriteria);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication1, $standardSingleCriteria, null)
            ->times($irhpPermitApplication1StandardRequired);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication1, $cabotageSingleCriteria, null)
            ->times($irhpPermitApplication1CabotageRequired);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication2, $standardMultipleCriteria, null)
            ->times($irhpPermitApplication2StandardRequired);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication3, null, null)
            ->times($irhpPermitApplication3MoroccoRequired);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication4, null, $irhpPermitApplication4ExpiryDate)
            ->times($irhpPermitApplication4MoroccoRequired);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication5, null, $irhpPermitApplication5ExpiryDate)
            ->times($irhpPermitApplication5MoroccoRequired);

        $this->mockedSmServices['PermitsAllocateIrhpPermitAllocator']->shouldReceive('allocate')
            ->with(m::type(Result::class), $irhpPermitApplication6, null, $irhpPermitApplication6ExpiryDate)
            ->times($irhpPermitApplication6MoroccoRequired);

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->irhpApplicationId,
            $result->getId('irhpApplication')
        );
    }

    public function testHandleCommandBilateralMoroccoException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown permit category: other_permit_cat');

        $irhpPermitApplicationFilteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => 4,
        ];
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getFilteredBilateralRequired')
            ->andReturn($irhpPermitApplicationFilteredBilateralRequired);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitCategory->getId')
            ->withNoArgs()
            ->andReturn('other_permit_cat');

        $irhpPermitApplications = new ArrayCollection([$irhpPermitApplication]);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturnNull();
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_BILATERAL);
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->sut->handleCommand($this->command);
    }

    public function testHandleCommandCandidatePermits(): void
    {
        $irhpPermitApplication1Id = 57;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getId')
            ->andReturn($irhpPermitApplication1Id);

        $irhpPermitApplications = new ArrayCollection([$irhpPermitApplication1]);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturnNull();
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS);
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('proceedToValid')
            ->with($this->refData[IrhpInterface::STATUS_VALID])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->expectedSideEffect(
            AllocateCandidatePermits::class,
            ['id' => $irhpPermitApplication1Id],
            new Result()
        );

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->irhpApplicationId,
            $result->getId('irhpApplication')
        );
    }

    public function testHandleCommandCandidatePermitsWithIssuedEmail(): void
    {
        $irhpPermitApplication1Id = 57;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getId')
            ->andReturn($irhpPermitApplication1Id);

        $irhpPermitApplications = new ArrayCollection([$irhpPermitApplication1]);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturn(SendEcmtApsgIssued::class);
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS);
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();
        $irhpApplication->shouldReceive('proceedToValid')
            ->with($this->refData[IrhpInterface::STATUS_VALID])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->expectedSideEffect(
            AllocateCandidatePermits::class,
            ['id' => $irhpPermitApplication1Id],
            new Result()
        );

        $this->expectedEmailQueueSideEffect(
            SendEcmtApsgIssued::class,
            ['id' => $this->irhpApplicationId],
            $this->irhpApplicationId,
            new Result()
        );

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->irhpApplicationId,
            $result->getId('irhpApplication')
        );
    }

    public function testHandleCommandUnknownAllocationMode(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown allocation mode: unknown-allocation-mode');

        $irhpPermitApplication1Id = 57;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getId')
            ->andReturn($irhpPermitApplication1Id);

        $irhpPermitApplications = new ArrayCollection([$irhpPermitApplication1]);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIssuedEmailCommand')
            ->andReturnNull();
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getAllocationMode')
            ->andReturn('unknown-allocation-mode');
        $this->repoMap['IrhpApplication']->shouldReceive('refresh')
            ->with($irhpApplication)
            ->once();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->sut->handleCommand($this->command);
    }
}

<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Summary;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\Application\Summary as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\QueryHandler\Application\Summary
 */
class SummaryTest extends QueryHandlerTestCase
{
    public $mockCaseRepo;
    /** @var  Summary */
    protected $sut;

    /** @var m\MockInterface */
    private $mockAppRepo;
    /** @var m\MockInterface */
    private $mockFeeRepo;

    public function setUp(): void
    {
        $this->sut = new Summary();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('Fee', Repository\Fee::class);
        $this->mockRepo('Cases', Repository\Cases::class);

        parent::setUp();

        $this->mockFeeRepo = $this->repoMap['Fee'];
        $this->mockAppRepo = $this->repoMap['Application'];
        $this->mockCaseRepo = $this->repoMap['Cases'];

        $this->mockAppRepo
            ->shouldReceive('getCategoryReference')
            ->zeroOrMoreTimes()
            ->andReturnUsing(
                fn($category) => $category
            )
            ->shouldReceive('getSubCategoryReference')
            ->andReturnUsing(
                fn($category) => $category
            );
    }

    public function testHandleQuery()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');
        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(true);
        $mockApplication->shouldReceive('getStatus')->andReturn($mockRefData);
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(0);
        $mockApplication->setAuthSignature(0);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(new \stdClass());

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');

        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'PRINT_SIGN_RETURN' => 'PRINT_SIGN_RETURN',
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC',
                        'markup-financial-standing-proof'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => true,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryDigitallySigned()
    {
        $query = Qry::create(['id' => 111]);

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('isDigitallySigned')->andReturn(true);
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(true);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setIsVariation(0);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setAuthSignature(0);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(new \stdClass());

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);

        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC',
                        'markup-financial-standing-proof'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => true,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryWithDocs()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setIsVariation(0);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setAuthSignature(0);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $docs = new ArrayCollection();
        $docs->add(m::mock());

        $mockApplication->shouldReceive('getApplicationDocuments')
            ->with(
                Entity\System\Category::CATEGORY_APPLICATION,
                Entity\System\Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL
            )
            ->andReturn($docs);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn([])->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'PRINT_SIGN_RETURN' => 'PRINT_SIGN_RETURN',
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => '',
                'outstandingFee' => false,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryWithPsv()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('isPsv')->andReturn(true);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(false);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(0);
        $mockApplication->setAuthSignature(0);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');

        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn([])->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'PRINT_SIGN_RETURN' => 'PRINT_SIGN_RETURN',
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => '',
                'outstandingFee' => false,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryWithDocs2()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setIsVariation(0);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setAuthSignature(0);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');

        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'PRINT_SIGN_RETURN' => 'PRINT_SIGN_RETURN',
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => false,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryNoNeedToSign()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(true);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(0);
        $mockApplication->setAuthSignature(1);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');

        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC',
                        'markup-financial-standing-proof'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => false,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryVariation()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_valid');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('getApplicationCompletion->getFinancialEvidenceStatus')
            ->andReturn(Entity\Application\Application::VARIATION_STATUS_UPDATED);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(true);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(1);
        $mockApplication->setAuthSignature(1);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);

        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC',
                        'markup-financial-standing-proof'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => false,
                'canWithdraw' => false,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryVariationUnchangedFinancialEvidence()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_valid');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('getApplicationCompletion->getFinancialEvidenceStatus')
            ->andReturn(Entity\Application\Application::VARIATION_STATUS_UNCHANGED);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(1);
        $mockApplication->setAuthSignature(1);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([$this->createMock(Entity\Cases\Cases::class), $this->createMock(Entity\Cases\Cases::class)]);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => false,
                'canWithdraw' => false,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryVariationUpdatedOcWithIncrease()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('getApplicationCompletion->getFinancialEvidenceStatus')
            ->andReturn(Entity\Application\Application::VARIATION_STATUS_UNCHANGED);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(true);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        /** @var Entity\Licence\LicenceOperatingCentre $loc */
        $loc = m::mock(Entity\Licence\LicenceOperatingCentre::class)->makePartial();
        $loc->setNoOfVehiclesRequired(10);

        $mockApplication->shouldReceive('getLicence->getLocByOc')->andReturn($loc);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('U');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(1);
        $mockApplication->setAuthSignature(1);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'SUPPLY_SUPPORTING_EVIDENCE' => [
                        'MISSING_EVIDENCE_OC'
                    ],
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => false,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryVariationUpdatedOcWithoutIncrease()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_valid');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('getApplicationCompletion->getFinancialEvidenceStatus')
            ->andReturn(Entity\Application\Application::VARIATION_STATUS_UNCHANGED);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(false);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        /** @var Entity\Licence\LicenceOperatingCentre $loc */
        $loc = m::mock(Entity\Licence\LicenceOperatingCentre::class)->makePartial();
        $loc->setNoOfVehiclesRequired(10);
        $loc->setNoOfTrailersRequired(10);

        $mockApplication->shouldReceive('getLicence->getLocByOc')->andReturn($loc);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();
        $tm1->setTmApplicationStatus(
            new Entity\System\RefData(Entity\Tm\TransportManagerApplication::STATUS_AWAITING_SIGNATURE)
        );
        $tm1->setAction('A');

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(1);
        $mockApplication->setAuthSignature(1);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->shouldReceive('getTransportManagers')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'APPROVE_TM' => 'APPROVE_TM'
                ],
                'reference' => 'ref',
                'outstandingFee' => false,
                'canWithdraw' => false,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryVariationWithNoApplicationData()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_valid');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->with(['licence', 'status'])->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('getApplicationCompletion->getFinancialEvidenceStatus')
            ->andReturn(Entity\Application\Application::VARIATION_STATUS_UNCHANGED);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(1);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_STANDARD_NATIONAL);
        $mockApplication->setOperatingCentres(new ArrayCollection());
        $mockApplication->setTransportManagers(new ArrayCollection());
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(null);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(false);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');

        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [],
                'reference' => 'ref',
                'outstandingFee' => false,
                'canWithdraw' => false,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryWisthSpecialRestrictedLicence()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_valid');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(false);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(0);
        $mockApplication->setAuthSignature(0);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_SPECIAL_RESTRICTED);
        $mockApplication->shouldReceive('getTransportManagers->matching')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(new \stdClass());

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');

        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'PRINT_SIGN_RETURN' => 'PRINT_SIGN_RETURN'
                ],
                'reference' => 'ref',
                'outstandingFee' => true,
                'canWithdraw' => false,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryOpenCases()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(false);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->setIsVariation(0);
        $mockApplication->setAuthSignature(0);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_SPECIAL_RESTRICTED);
        $mockApplication->shouldReceive('getTransportManagers->matching')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(new \stdClass());

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn([$this->createMock(Entity\Cases\Cases::class), $this->createMock(Entity\Cases\Cases::class)]);
        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'PRINT_SIGN_RETURN' => 'PRINT_SIGN_RETURN'
                ],
                'reference' => 'ref',
                'outstandingFee' => true,
                'canWithdraw' => false,
                'wasAutoGranted'=>false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryZeroOpenCases()
    {
        $query = Qry::create(['id' => 111]);

        $mockRefData = m::mock(Entity\System\RefData::class);
        $mockRefData->shouldReceive('getId')->andReturn('apsts_consideration');

        /** @var Entity\Application\Application|m\MockInterface $mockApplication */
        $mockApplication = m::mock(Entity\Application\Application::class)->makePartial();
        $mockApplication->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canAddOperatingCentresEvidence')->andReturn(false);
        $mockApplication->shouldReceive('canAddFinancialEvidence')->andReturn(false);

        $mockApplication->shouldReceive('getStatus')->andReturn(
            $mockRefData
        );
        $mockApplication->shouldReceive('getLicence->getId')->andReturn(1);

        $tm1 = m::mock(Entity\Tm\TransportManagerApplication::class)->makePartial();

        $tms = new ArrayCollection();
        $tms->add($tm1);

        $mockApplication->setId(111);
        $mockApplication->setIsVariation(0);
        $mockApplication->setAuthSignature(0);
        $mockApplication->setWasAutoGranted(false);
        $mockApplication->shouldReceive('getLicenceType->getId')
            ->andReturn(Entity\Licence\Licence::LICENCE_TYPE_SPECIAL_RESTRICTED);
        $mockApplication->shouldReceive('getTransportManagers->matching')->andReturn($tms);
        $mockApplication->setDocuments(new ArrayCollection());
        $mockApplication->shouldReceive('getLatestOutstandingApplicationFee')->andReturn(new \stdClass());

        $this->mockAppRepo->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($mockApplication);
        $this->mockAppRepo->shouldReceive('getRefdataReference->getId')
            ->once()
            ->andReturn('apsts_consideration');
        $this->mockCaseRepo->shouldReceive('fetchOpenCasesForApplication')->with(111)->andReturn(
            []
        );
        $mockFee = m::mock()->shouldReceive('getLatestPaymentRef')->andReturn('ref')->once()->getMock();
        $this->mockFeeRepo->shouldReceive('fetchLatestPaidFeeByApplicationId')->with(111)->andReturn($mockFee)->once();

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'actions' => [
                    'PRINT_SIGN_RETURN' => 'PRINT_SIGN_RETURN'
                ],
                'reference' => 'ref',
                'outstandingFee' => true,
                'canWithdraw' => true,
                'wasAutoGranted' => false,
                'autoGrantChanges' => []
            ],
            $result->serialize()
        );
    }
}

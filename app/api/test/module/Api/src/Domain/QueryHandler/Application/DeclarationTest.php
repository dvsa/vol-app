<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Declaration;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationUndertakingsReviewService;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SystemParameterEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Transfer\Query\Application\Declaration as Qry;
use Dvsa\Olcs\Api\Service\FeesHelperService;
use Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * DeclarationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeclarationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Declaration();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);
        $this->mockRepo('Fee', FeeRepo::class);
        $this->mockRepo('FeeType', FeeTypeRepo::class);
        $this->mockedSmServices = [
            'FeesHelperService' => m::mock(FeesHelperService::class),
            'SectionAccessService' => m::mock(SectionAccessService::class),
            'Review\ApplicationUndertakings' => m::mock(ApplicationUndertakingsReviewService::class),
        ];

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Qry::create(['id' => 111]);

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ->shouldReceive('getId')
            ->andReturn(111)
            ->twice()
            ->shouldReceive('getVariationCompletion')
            ->andReturn('foo')
            ->once()
            ->shouldReceive('getDigitalSignature')
            ->andReturn(null)
            ->once()
            ->getMock();

        $mockFee = m::mock()
            ->shouldReceive('getGrossAmount')
            ->andReturn(123.45)
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockApplication);

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->once()
            ->andReturn([$mockFee])
            ->once();

        $mockApplication->shouldReceive('serialize')->twice()->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canHaveInterimLicence')->with()->once()->andReturn(true);
        $mockApplication->shouldReceive('isLicenceUpgrade')->with()->once()->andReturn('yyy');
        $mockApplication->shouldReceive('isGoods')->with()->once()->andReturn(true);

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getTotalOutstandingFeeAmountForApplication')
            ->with(111)
            ->andReturn(123.45)
            ->once()
            ->getMock();

        $this->mockedSmServices['SectionAccessService']
            ->shouldReceive('getAccessibleSections')
            ->with($mockApplication)
            ->andReturn('bar')
            ->once()
            ->getMock();

        $this->mockedSmServices['Review\ApplicationUndertakings']
            ->shouldReceive('getMarkup')
            ->with(['foo' => 'bar', 'isGoods' => true, 'isInternal' => false])
            ->once()
            ->andReturn('markup')
            ->getMock();

        $expected = [
            'foo' => 'bar',
            'canHaveInterimLicence' => true,
            'isLicenceUpgrade' => 'yyy',
            'outstandingFeeTotal' => 123.45,
            'sections' => 'bar',
            'variationCompletion' => 'foo',
            'declarations' => 'markup',
            'signature' => [],
            'interimFee' => 123.45
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryWithSignature()
    {
        $query = Qry::create(['id' => 111]);

        $mockDigitalSignature = m::mock();
        $mockDigitalSignature->shouldReceive('getSignatureName')->with()->once()->andReturn('Bob Smith');
        $mockDigitalSignature->shouldReceive('getCreatedOn')->with()->once()->andReturn('CREATED_ON');
        $mockDigitalSignature->shouldReceive('getDateOfBirth')->with()->once()->andReturn('DOB');

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class);
        $mockApplication->shouldReceive('getId')->twice()->andReturn(111);
        $mockApplication->shouldReceive('getVariationCompletion')->once()->andReturn('foo');
        $mockApplication->shouldReceive('getDigitalSignature')->with()->atLeast(1)->andReturn($mockDigitalSignature);

        $mockFee = m::mock()
            ->shouldReceive('getGrossAmount')
            ->andReturn(123.45)
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockApplication);

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->once()
            ->andReturn([$mockFee])
            ->once();

        $mockApplication->shouldReceive('serialize')->twice()->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canHaveInterimLicence')->with()->once()->andReturn(true);
        $mockApplication->shouldReceive('isLicenceUpgrade')->with()->once()->andReturn('yyy');
        $mockApplication->shouldReceive('isGoods')->with()->once()->andReturn(true);

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getTotalOutstandingFeeAmountForApplication')
            ->with(111)
            ->andReturn(123.45)
            ->once()
            ->getMock();

        $this->mockedSmServices['SectionAccessService']
            ->shouldReceive('getAccessibleSections')
            ->with($mockApplication)
            ->andReturn('bar')
            ->once()
            ->getMock();

        $this->mockedSmServices['Review\ApplicationUndertakings']
            ->shouldReceive('getMarkup')
            ->with(['foo' => 'bar', 'isGoods' => true, 'isInternal' => false])
            ->once()
            ->andReturn('markup')
            ->getMock();

        $expected = [
            'foo' => 'bar',
            'canHaveInterimLicence' => true,
            'isLicenceUpgrade' => 'yyy',
            'outstandingFeeTotal' => 123.45,
            'sections' => 'bar',
            'variationCompletion' => 'foo',
            'declarations' => 'markup',
            'signature' => [
                'name' => 'Bob Smith',
                'date' => 'CREATED_ON',
                'dob' => 'DOB',
            ],
            'interimFee' => 123.45
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryInterimFeeNotExist()
    {
        $query = Qry::create(['id' => 111]);

        $mockFeeTypeInterim = m::mock(RefDataEntity::class);
        $mockGoodsOrPsv = m::mock(RefDataEntity::class);
        $mockLicenceType = m::mock(RefDataEntity::class);
        $createdOn = '2017-01-01';

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ->shouldReceive('getId')
            ->andReturn(111)
            ->twice()
            ->shouldReceive('getVariationCompletion')
            ->andReturn('foo')
            ->once()
            ->shouldReceive('getDigitalSignature')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn($mockGoodsOrPsv)
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn($mockLicenceType)
            ->once()
            ->shouldReceive('getCreatedOn')
            ->andReturn($createdOn)
            ->once()
            ->shouldReceive('getNiFlag')
            ->andReturn('Y')
            ->once()
            ->shouldReceive('hasHgvAuthorisationIncreased')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockApplication)
            ->shouldReceive('getRefdataReference')
            ->with(FeeTypeEntity::FEE_TYPE_GRANTINT)
            ->andReturn($mockFeeTypeInterim)
            ->once()
            ->shouldReceive('getReference')
            ->with(TrafficAreaEntity::class, TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE)
            ->andReturn('N')
            ->once();

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->once()
            ->andReturn([])
            ->once();

        $mockFeeType = m::mock()
            ->shouldReceive('getFixedValue')
            ->twice()
            ->andReturn(123.45)
            ->getMock();

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')
            ->with(
                $mockFeeTypeInterim,
                $mockGoodsOrPsv,
                $mockLicenceType,
                m::type(\DateTime::class),
                'N',
                true
            )
            ->once()
            ->andReturn($mockFeeType)
            ->once();

        $mockApplication->shouldReceive('serialize')->twice()->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canHaveInterimLicence')->with()->once()->andReturn(true);
        $mockApplication->shouldReceive('isLicenceUpgrade')->with()->once()->andReturn('yyy');
        $mockApplication->shouldReceive('isGoods')->with()->once()->andReturn(true);

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getTotalOutstandingFeeAmountForApplication')
            ->with(111)
            ->andReturn(123.45)
            ->once()
            ->getMock();

        $this->mockedSmServices['SectionAccessService']
            ->shouldReceive('getAccessibleSections')
            ->with($mockApplication)
            ->andReturn('bar')
            ->once()
            ->getMock();

        $this->mockedSmServices['Review\ApplicationUndertakings']
            ->shouldReceive('getMarkup')
            ->with(['foo' => 'bar', 'isGoods' => true, 'isInternal' => false])
            ->once()
            ->andReturn('markup')
            ->getMock();

        $expected = [
            'foo' => 'bar',
            'canHaveInterimLicence' => true,
            'isLicenceUpgrade' => 'yyy',
            'outstandingFeeTotal' => 123.45,
            'sections' => 'bar',
            'variationCompletion' => 'foo',
            'declarations' => 'markup',
            'signature' => [],
            'interimFee' => 123.45
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryInterimFeeNotExistAndNotApplicable()
    {
        $query = Qry::create(['id' => 111]);

        $mockFeeTypeInterim = m::mock(RefDataEntity::class);
        $mockGoodsOrPsv = m::mock(RefDataEntity::class);
        $mockLicenceType = m::mock(RefDataEntity::class);
        $createdOn = '2017-01-01';

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ->shouldReceive('getId')
            ->andReturn(111)
            ->twice()
            ->shouldReceive('getVariationCompletion')
            ->andReturn('foo')
            ->once()
            ->shouldReceive('getDigitalSignature')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn($mockGoodsOrPsv)
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn($mockLicenceType)
            ->once()
            ->shouldReceive('getCreatedOn')
            ->andReturn($createdOn)
            ->once()
            ->shouldReceive('getNiFlag')
            ->andReturn('Y')
            ->once()
            ->shouldReceive('hasHgvAuthorisationIncreased')
            ->andReturn(false)
            ->once()
            ->shouldReceive('hasLgvAuthorisationIncreased')
            ->once()
            ->andReturn(false)
            ->shouldReceive('hasAuthTrailersIncrease')
            ->andReturn(false)
            ->once()
            ->shouldReceive('hasNewOperatingCentre')
            ->andReturn(false)
            ->once()
            ->shouldReceive('hasIncreaseInOperatingCentre')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockApplication)
            ->shouldReceive('getRefdataReference')
            ->with(FeeTypeEntity::FEE_TYPE_GRANTINT)
            ->andReturn($mockFeeTypeInterim)
            ->once()
            ->shouldReceive('getReference')
            ->with(TrafficAreaEntity::class, TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE)
            ->andReturn('N')
            ->once();

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->once()
            ->andReturn([])
            ->once();

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')
            ->with(
                $mockFeeTypeInterim,
                $mockGoodsOrPsv,
                $mockLicenceType,
                m::type(\DateTime::class),
                'N',
                true
            )
            ->once()
            ->andReturn(true)
            ->once();

        $mockApplication->shouldReceive('serialize')->twice()->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canHaveInterimLicence')->with()->once()->andReturn(true);
        $mockApplication->shouldReceive('isLicenceUpgrade')->with()->once()->andReturn('yyy');
        $mockApplication->shouldReceive('isGoods')->with()->once()->andReturn(true);

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getTotalOutstandingFeeAmountForApplication')
            ->with(111)
            ->andReturn(123.45)
            ->once()
            ->getMock();

        $this->mockedSmServices['SectionAccessService']
            ->shouldReceive('getAccessibleSections')
            ->with($mockApplication)
            ->andReturn('bar')
            ->once()
            ->getMock();

        $this->mockedSmServices['Review\ApplicationUndertakings']
            ->shouldReceive('getMarkup')
            ->with(['foo' => 'bar', 'isGoods' => true, 'isInternal' => false])
            ->once()
            ->andReturn('markup')
            ->getMock();

        $expected = [
            'foo' => 'bar',
            'canHaveInterimLicence' => true,
            'isLicenceUpgrade' => 'yyy',
            'outstandingFeeTotal' => 123.45,
            'sections' => 'bar',
            'variationCompletion' => 'foo',
            'declarations' => 'markup',
            'signature' => [],
            'interimFee' => null
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryInterimFeeTypeNotExist()
    {
        $query = Qry::create(['id' => 111]);

        $mockFeeTypeInterim = m::mock(RefDataEntity::class);
        $mockGoodsOrPsv = m::mock(RefDataEntity::class);
        $mockLicenceType = m::mock(RefDataEntity::class);
        $createdOn = '2017-01-01';

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ->shouldReceive('getId')
            ->andReturn(111)
            ->twice()
            ->shouldReceive('getVariationCompletion')
            ->andReturn('foo')
            ->once()
            ->shouldReceive('getDigitalSignature')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn($mockGoodsOrPsv)
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn($mockLicenceType)
            ->once()
            ->shouldReceive('getCreatedOn')
            ->andReturn($createdOn)
            ->once()
            ->shouldReceive('getNiFlag')
            ->andReturn('Y')
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockApplication)
            ->shouldReceive('getRefdataReference')
            ->with(FeeTypeEntity::FEE_TYPE_GRANTINT)
            ->andReturn($mockFeeTypeInterim)
            ->once()
            ->shouldReceive('getReference')
            ->with(TrafficAreaEntity::class, TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE)
            ->andReturn('N')
            ->once();

        $this->repoMap['Fee']->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(111, true)
            ->once()
            ->andReturn([])
            ->once();

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')
            ->with(
                $mockFeeTypeInterim,
                $mockGoodsOrPsv,
                $mockLicenceType,
                m::type(\DateTime::class),
                'N',
                true
            )
            ->once()
            ->andReturnNull()
            ->once();

        $mockApplication->shouldReceive('serialize')->twice()->andReturn(['foo' => 'bar']);
        $mockApplication->shouldReceive('canHaveInterimLicence')->with()->once()->andReturn(true);
        $mockApplication->shouldReceive('isLicenceUpgrade')->with()->once()->andReturn('yyy');
        $mockApplication->shouldReceive('isGoods')->with()->once()->andReturn(true);

        $this->mockedSmServices['FeesHelperService']
            ->shouldReceive('getTotalOutstandingFeeAmountForApplication')
            ->with(111)
            ->andReturn(123.45)
            ->once()
            ->getMock();

        $this->mockedSmServices['SectionAccessService']
            ->shouldReceive('getAccessibleSections')
            ->with($mockApplication)
            ->andReturn('bar')
            ->once()
            ->getMock();

        $this->mockedSmServices['Review\ApplicationUndertakings']
            ->shouldReceive('getMarkup')
            ->with(['foo' => 'bar', 'isGoods' => true, 'isInternal' => false])
            ->once()
            ->andReturn('markup')
            ->getMock();

        $expected = [
            'foo' => 'bar',
            'canHaveInterimLicence' => true,
            'isLicenceUpgrade' => 'yyy',
            'outstandingFeeTotal' => 123.45,
            'sections' => 'bar',
            'variationCompletion' => 'foo',
            'declarations' => 'markup',
            'signature' => [],
            'interimFee' => null
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }
}

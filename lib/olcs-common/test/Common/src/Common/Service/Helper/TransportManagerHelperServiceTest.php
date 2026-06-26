<?php

namespace CommonTest\Helper;

use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Common\Service\Data\CategoryDataService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;

/**
 * @covers \Common\Service\Helper\TransportManagerHelperService
 */
class TransportManagerHelperServiceTest extends MockeryTestCase
{
    /** @var TransportManagerHelperService */
    protected $sut;

    /** @var TransferAnnotationBuilder */
    private $transferAnnotationBuilder;

    /** @var QueryService */
    private $queryService;

    /** @var FormHelperService */
    private $formHelper;

    /** @var DateHelperService */
    private $dateHelper;

    /** @var TranslationHelperService */
    private $translationHelper;

    /** @var UrlHelperService */
    private $urlHelper;

    /** @var TableFactory */
    private $tableService;

    /** @var QueryContainerInterface */
    private $query;

    #[\Override]
    protected function setUp(): void
    {
        $this->transferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class);
        $this->queryService = m::mock(QueryService::class);
        $this->formHelper = m::mock(FormHelperService::class);
        $this->dateHelper = m::mock(DateHelperService::class);
        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->tableService = m::mock(TableFactory::class);
        $this->query = m::mock(QueryContainerInterface::class);

        $this->sut = new TransportManagerHelperService(
            $this->transferAnnotationBuilder,
            $this->queryService,
            $this->formHelper,
            $this->dateHelper,
            $this->translationHelper,
            $this->urlHelper,
            $this->tableService
        );
    }

    public function testGetCertificateFileData(): void
    {
        $tmId = 111;
        $file = ['name' => 'foo.txt'];

        $expected = [
            'transportManager' => 111,
            'description' => 'foo.txt',
            'issuedDate' => '2015-01-01 10:10:10',
            'category' => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
        ];

        $this->dateHelper->shouldReceive('getDate')
            ->andReturn('2015-01-01 10:10:10')
            ->once();

        $response = $this->sut->getCertificateFileData($tmId, $file);

        $this->assertEquals($expected, $response);
    }

    public function testRemoveTmTypeBothOption(): void
    {
        /** @var Element $mockTmTypeField */
        $mockTmTypeField = m::mock(Element::class);

        $this->formHelper->shouldReceive('removeOption')->once()->with($mockTmTypeField, 'tm_t_b');

        $this->sut->removeTmTypeBothOption($mockTmTypeField);
    }

    public function testPopulateOtherLicencesTable(): void
    {
        /** @var TableBuilder $otherLicencesTable */
        $otherLicencesTable = m::mock(TableBuilder::class);

        /** @var Fieldset $mockOtherLicenceField */
        $mockOtherLicenceField = m::mock(Fieldset::class);

        $this->formHelper->shouldReceive('populateFormTable')
            ->once()
            ->with($mockOtherLicenceField, $otherLicencesTable);

        $this->sut->populateOtherLicencesTable($mockOtherLicenceField, $otherLicencesTable);
    }

    public function testGetResponsibilityFileData(): void
    {
        $tmId = 111;

        $expected = [
            'transportManager' => 111,
            'issuedDate' => '2014-01-20 10:10:10',
            'category' => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
        ];

        // Expectations
        $this->dateHelper->shouldReceive('getDate')
            ->with(\DateTime::W3C)
            ->andReturn('2014-01-20 10:10:10');

        // Assertions
        $data = $this->sut->getResponsibilityFileData($tmId);

        $this->assertEquals($expected, $data);
    }

    public function testGetConvictionsAndPenaltiesTable(): void
    {
        $tmId = 111;

        $tableData = [
            'foo' => 'bar'
        ];
        $mockTable = $this->expectedGetConvictionsAndPenaltiesTable($tableData);

        $this->assertSame($mockTable, $this->sut->getConvictionsAndPenaltiesTable($tmId));
    }

    public function testGetPreviousLicencesTable(): void
    {
        $tmId = 111;

        $tableData = [
            'foo' => 'bar'
        ];
        $mockTable = $this->expectGetPreviousLicencesTable($tableData);

        $this->assertSame($mockTable, $this->sut->getPreviousLicencesTable($tmId));
    }

    public function testAlterPreviousHistoryFieldsetTm(): void
    {
        $fieldset = m::mock(\Laminas\Form\Fieldset::class);
        $hasConvictions = m::mock(\Laminas\Form\Fieldset::class);
        $hasConvictions->shouldReceive('unsetValueOption')->with('Y');
        $hasConvictions->shouldReceive('unsetValueOption')->with('N');
        $hasConvictions->shouldReceive('setOption')->with('hint', 'string');
        $convictions = m::mock(\Laminas\Form\Fieldset::class);
        $convictions->shouldReceive('removeAttribute')->with('class');
        $hasPreviousLicences = m::mock(\Laminas\Form\Fieldset::class);
        $hasPreviousLicences->shouldReceive('unsetValueOption')->with('Y');
        $hasPreviousLicences->shouldReceive('unsetValueOption')->with('N');
        $previousLicences = m::mock(\Laminas\Form\Fieldset::class);
        $previousLicences->shouldReceive('removeAttribute')->with('class');
        $fieldset->shouldReceive('get')->with('hasConvictions')->andReturn($hasConvictions);
        $fieldset->shouldReceive('get')->with('convictions')->andReturn($convictions);
        $fieldset->shouldReceive('get')->with('previousLicences')->andReturn($previousLicences);
        $fieldset->shouldReceive('get')->with('hasPreviousLicences')->andReturn($hasPreviousLicences);

        $mockResponse = m::mock();

        // Expectations
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(\Dvsa\Olcs\Transfer\Query\Tm\TransportManager::class)
            ->andReturn($this->query);

        $mockResponse->shouldReceive('isOk')
            ->andReturn(true);

        $this->queryService->shouldReceive('send')
            ->with($this->query)
            ->andReturn($mockResponse);

        $tm = [
            'previousConvictions' => [
                'foo' => 'bar'
            ],
            'otherLicences' => [
                'foo' => 'bar'
            ]
        ];
        $convictionTable = $this->expectedGetConvictionsAndPenaltiesTable($tm['previousConvictions']);
        $licenceTable = $this->expectGetPreviousLicencesTable($tm['otherLicences']);

        $this->formHelper->shouldReceive('populateFormTable')
            ->once()
            ->with($convictions, $convictionTable, 'convictions')
            ->shouldReceive('populateFormTable')
            ->once()
            ->with($previousLicences, $licenceTable, 'previousLicences');

        $this->translationHelper->shouldReceive('translate')->andReturn('string');
        $this->translationHelper->shouldReceive('translateReplace')->andReturn('string');

        $this->urlHelper->shouldReceive('fromRoute')->andReturn('string');

        $this->sut->alterPreviousHistoryFieldsetTm($fieldset, $tm);
    }

    public function testAlterPreviousHistoryFieldset(): void
    {
        $fieldset = m::mock(\Laminas\Form\Fieldset::class);
        $hasConvictions = m::mock(\Laminas\Form\Fieldset::class);
        $hasConvictions->shouldReceive('unsetValueOption')->with('Y');
        $hasConvictions->shouldReceive('unsetValueOption')->with('N');
        $hasConvictions->shouldReceive('setOption')->with('hint', 'string');
        $convictions = m::mock(\Laminas\Form\Fieldset::class);
        $convictions->shouldReceive('removeAttribute')->with('class');
        $hasPreviousLicences = m::mock(\Laminas\Form\Fieldset::class);
        $hasPreviousLicences->shouldReceive('unsetValueOption')->with('Y');
        $hasPreviousLicences->shouldReceive('unsetValueOption')->with('N');
        $previousLicences = m::mock(\Laminas\Form\Fieldset::class);
        $previousLicences->shouldReceive('removeAttribute')->with('class');
        $fieldset->shouldReceive('get')->with('hasConvictions')->andReturn($hasConvictions);
        $fieldset->shouldReceive('get')->with('convictions')->andReturn($convictions);
        $fieldset->shouldReceive('get')->with('previousLicences')->andReturn($previousLicences);
        $fieldset->shouldReceive('get')->with('hasPreviousLicences')->andReturn($hasPreviousLicences);

        $tmId = 111;

        $mockResponse = m::mock();

        // Expectations
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(\Dvsa\Olcs\Transfer\Query\Tm\TransportManager::class)
            ->andReturn($this->query);

        $mockResponse->shouldReceive('isOk')
            ->andReturn(true);
        $mockResponse->shouldReceive('getResult')
            ->andReturn(['id' => $tmId, 'removedDate' => null]);

        $this->queryService->shouldReceive('send')
            ->with($this->query)
            ->andReturn($mockResponse);

        $tm = [
            'previousConvictions' => [
                'foo' => 'bar'
            ],
            'otherLicences' => [
                'foo' => 'bar'
            ]
        ];
        $convictionTable = $this->expectedGetConvictionsAndPenaltiesTable($tm['previousConvictions']);
        $licenceTable = $this->expectGetPreviousLicencesTable($tm['otherLicences']);

        $this->formHelper->shouldReceive('populateFormTable')
            ->once()
            ->with($convictions, $convictionTable, 'convictions')
            ->shouldReceive('populateFormTable')
            ->once()
            ->with($previousLicences, $licenceTable, 'previousLicences');

        $this->translationHelper->shouldReceive('translate')->andReturn('string');
        $this->translationHelper->shouldReceive('translateReplace')->andReturn('string');

        $this->urlHelper->shouldReceive('fromRoute')->andReturn('string');

        $this->sut->alterPreviousHistoryFieldset($fieldset, $tmId);
    }

    protected function expectedGetConvictionsAndPenaltiesTable(array $tableData): TableBuilder
    {
        // Mocks
        $mockTable = m::mock(TableBuilder::class);

        $mockResponse = m::mock();

        $query = m::mock(QueryContainerInterface::class);

        // Expectations
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(\Dvsa\Olcs\Transfer\Query\PreviousConviction\GetList::class)
            ->andReturn($query);

        $mockResponse->shouldReceive('isOk')
            ->andReturn(true);
        $mockResponse->shouldReceive('getResult')
            ->andReturn(['results' => $tableData]);

        $this->queryService->shouldReceive('send')
            ->with($query)
            ->andReturn($mockResponse);

        $this->tableService->shouldReceive('prepareTable')
            ->once()
            ->with('tm.convictionsandpenalties', $tableData)
            ->andReturn($mockTable);

        return $mockTable;
    }

    protected function expectGetPreviousLicencesTable(array $tableData): TableBuilder
    {
        // Mocks
        $mockTable = m::mock(TableBuilder::class);

        $mockResponse = m::mock();

        $query = m::mock(QueryContainerInterface::class);

        // Expectations
        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(\Dvsa\Olcs\Transfer\Query\OtherLicence\GetList::class)
            ->andReturn($query);

        $mockResponse->shouldReceive('isOk')
            ->andReturn(true);
        $mockResponse->shouldReceive('getResult')
            ->andReturn(['results' => $tableData]);

        $this->queryService->shouldReceive('send')
            ->with($query)
            ->andReturn($mockResponse);

        $this->tableService->shouldReceive('prepareTable')
            ->once()
            ->with('tm.previouslicences', $tableData)
            ->andReturn($mockTable);

        return $mockTable;
    }
}

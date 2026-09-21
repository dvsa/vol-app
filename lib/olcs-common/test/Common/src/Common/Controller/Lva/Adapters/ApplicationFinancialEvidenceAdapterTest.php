<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\ApplicationFinancialEvidenceAdapter;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Data\CategoryDataService as Category;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class ApplicationFinancialEvidenceAdapterTest extends MockeryTestCase
{
    protected $sut;

    protected $container;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);
        $this->sut = m::mock(ApplicationFinancialEvidenceAdapter::class, [$this->container])->makePartial();
    }

    public function testAlterFormForLva(): void
    {
        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('finance')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('requiredFinance')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setValue')
                            ->with('markup-required-finance-application')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertNull($this->sut->alterFormForLva($mockForm));
    }

    public function testGetDocuments(): void
    {
        $applicationId = 1;

        $this->sut->shouldReceive('getData')
            ->with($applicationId)
            ->andReturn(['documents' => ['documents']])
            ->once();

        $this->assertEquals(['documents'], $this->sut->getDocuments($applicationId));
    }

    public function testGetUploadMetaData(): void
    {
        $applicationId = 1;
        $licenceId = 2;
        $file = [
            'name' => 'foo'
        ];

        $this->sut->shouldReceive('getData')
            ->with($applicationId)
            ->andReturn(['licence' => ['id' => $licenceId]])
            ->once();

        $expected = [
            'application' => $applicationId,
            'description' => $file['name'],
            'category'    => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL,
            'licence'     => $licenceId,
        ];

        $this->assertEquals($expected, $this->sut->getUploadMetaData($file, $applicationId));
    }

    public function testGetData(): void
    {
        $applicationId = 1;

        $this->container->shouldReceive('get')
            ->with(AnnotationBuilder::class)
            ->andReturn(
                m::mock()
                    ->shouldReceive('createQuery')
                    ->andReturn('query')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with(CachingQueryService::class)
            ->andReturn(
                m::mock()
                    ->shouldReceive('send')
                    ->with('query')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getResult')
                            ->once()
                            ->andReturn('foo')
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->assertEquals('foo', $this->sut->getData($applicationId, true));
        // testing cache
        $this->assertEquals('foo', $this->sut->getData($applicationId, false));
    }

    public function testGetDocumentsMergesAnalysisStatus(): void
    {
        $applicationId = 1;

        $this->sut->shouldReceive('getData')
            ->with($applicationId)
            ->andReturn(['documents' => [
                ['id' => 101, 'description' => 'doc-a.pdf'],
                ['id' => 102, 'description' => 'doc-b.pdf'],
            ]])
            ->once();

        $this->sut->shouldReceive('getAnalysesByDocumentId')
            ->with($applicationId)
            ->andReturn([
                101 => ['status' => 'SUCCESS'],
                // no entry for 102
            ])
            ->once();

        $result = $this->sut->getDocuments($applicationId);

        $this->assertEquals('SUCCESS', $result[0]['analysisStatus']);
        $this->assertNull($result[1]['analysisStatus']);
    }

    public function testGetDocumentsReturnsEmptyArrayWhenDocumentsIsNotArray(): void
    {
        $applicationId = 1;

        $this->sut->shouldReceive('getData')
            ->with($applicationId)
            ->andReturn(['documents' => null])
            ->once();

        $this->sut->shouldReceive('getAnalysesByDocumentId')
            ->with($applicationId)
            ->andReturn([])
            ->once();

        $this->assertEquals([], $this->sut->getDocuments($applicationId));
    }

    public function testGetAnalysesByDocumentIdReturnsEmptyArrayWhenResponseNotOk(): void
    {
        $applicationId = 1;

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(false)->once()
            ->getMock();

        $this->container->shouldReceive('get')
            ->with(AnnotationBuilder::class)
            ->andReturn(
                m::mock()->shouldReceive('createQuery')->andReturn('query')->once()->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with(CachingQueryService::class)
            ->andReturn(
                m::mock()->shouldReceive('send')->with('query')->andReturn($mockResponse)->once()->getMock()
            )
            ->once()
            ->getMock();

        $method = new \ReflectionMethod($this->sut, 'getAnalysesByDocumentId');
        $method->setAccessible(true);

        $this->assertEquals([], $method->invoke($this->sut, $applicationId));
    }

    public function testGetAnalysesByDocumentIdIndexesByDocumentId(): void
    {
        $applicationId = 1;

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn([
                'analyses' => [
                    ['documentId' => 101, 'status' => 'SUCCESS'],
                    ['documentId' => 102, 'status' => 'PENDING'],
                ],
            ])->once()
            ->getMock();

        $this->container->shouldReceive('get')
            ->with(AnnotationBuilder::class)
            ->andReturn(
                m::mock()->shouldReceive('createQuery')->andReturn('query')->once()->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with(CachingQueryService::class)
            ->andReturn(
                m::mock()->shouldReceive('send')->with('query')->andReturn($mockResponse)->once()->getMock()
            )
            ->once()
            ->getMock();

        $method = new \ReflectionMethod($this->sut, 'getAnalysesByDocumentId');
        $method->setAccessible(true);

        $result = $method->invoke($this->sut, $applicationId);

        $this->assertEquals(['status' => 'SUCCESS'], $result[101]);
        $this->assertEquals(['status' => 'PENDING'], $result[102]);
    }
}

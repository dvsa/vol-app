<?php

declare(strict_types=1);

namespace CommonTest\Controller\Traits;

use Common\Form\Form;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FormHelperService;
use CommonTest\Common\Controller\Traits\Stubs\CompanySearchStub;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber;
use Laminas\Http\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class CompanySearchTest extends MockeryTestCase
{
    protected CompanySearchStub $sut;

    /** @var  m\MockInterface */
    protected $mockResp;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new CompanySearchStub();
        $this->mockResp = m::mock(Response::class);
        $this->sut->stubResponse = $this->mockResp;
    }

    public function testCompanySearch(): void
    {
        $mockHelperService = m::mock(FormHelperService::class);
        $form = new Form();
        $data = [
            'detailsFieldset' => 'detailsFieldset',
            'addressFieldset' => 'addressFieldset',
            'companyNumber' => 1
        ];
        $mockHelperService->shouldReceive('processCompanyNumberLookupForm')->andReturn($data);
        $this->mockResp->shouldReceive('isOk')->andReturn(true);
        $this->mockResp->shouldReceive('getResult')->andReturn($data);

        $actual = $this->sut->populateCompanyDetails(
            $mockHelperService,
            $form,
            $data['detailsFieldset'],
            $data['addressFieldset'],
            $data['companyNumber']
        );
        $dto = $this->sut->stubResponse->dto;
        $this->assertInstanceOf(ByNumber::class, $dto);
        $this->assertSame($actual, $form);
    }

    public function testCompanySearchNoDataReturned(): void
    {
        $mockHelperService = m::mock(FormHelperService::class);
        $data = [];
        $mockHelperService->shouldReceive('processCompanyNumberLookupForm')->andReturn($data);
        $form = new Form();

        $data = [
            'detailsFieldset' => 'detailsFieldset',
            'addressFieldset' => 'addressFieldset',
            'companyNumber' => 1234567891
        ];

        $this->mockResp->shouldReceive('isOk')->andReturn(false);
        $mockHelperService->shouldReceive('setCompanyNotFoundError')->once()->with($form, $data['detailsFieldset']);
        $actual = $this->sut->populateCompanyDetails(
            $mockHelperService,
            $form,
            $data['detailsFieldset'],
            $data['addressFieldset'],
            $data['companyNumber']
        );
        $this->assertSame($actual, $form);
    }

    public function testCompanySearchNotFound(): void
    {
        $mockHelperService = m::mock(FormHelperService::class);
        $data = [];
        $mockHelperService->shouldReceive('processCompanyNumberLookupForm')->andReturn($data);
        $form = new Form();

        $data = [
            'detailsFieldset' => 'detailsFieldset',
            'addressFieldset' => 'addressFieldset',
            'companyNumber' => 1234567891
        ];

        $this->sut = m::mock(CompanySearchStub::class)->makePartial();
        $this->sut->shouldReceive('handleQuery')->andThrow(NotFoundException::class);

        $mockHelperService->shouldReceive('setCompanyNotFoundError')->once()->with($form, $data['detailsFieldset']);
        $actual = $this->sut->populateCompanyDetails(
            $mockHelperService,
            $form,
            $data['detailsFieldset'],
            $data['addressFieldset'],
            $data['companyNumber']
        );
        $this->assertSame($actual, $form);
    }

    /**
     * @dataProvider dpCompanyNumbers
     */
    public function testisValidCompanyNumber($comanyNumber, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValidCompanyNumber($comanyNumber));
    }

    public function dpCompanyNumbers(): array
    {
        return [
            'valid' => [
                12345678,
                true
            ],
            'invalid' => [
                123,
                false
            ]
        ];
    }
}

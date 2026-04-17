<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException as DomainNotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse\ByNumber;
use Dvsa\Olcs\CompaniesHouse\Service\Client;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\NotFoundException;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\ServiceException;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class ByNumberTest extends QueryHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        /**
         * @var ByNumber
         */
        $this->sut = new ByNumber();

        $this->mockedSmServices[Client::class] = m::mock(Client::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $queryData = ['companyNumber' => "00000001"];
        $this->mockedSmServices[Client::class]->shouldReceive('getCompanyProfile')
            ->with($queryData['companyNumber'])
            ->once()
            ->andReturn(['test']);

        $query = (new \Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber())->create($queryData);
        $actual = $this->sut->handleQuery($query);

        $expected = [
            'count' => 1,
            'result' => [['test']]
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testExpectedExceptionWhenCompanyNotFound(): void
    {
        $queryData = ['companyNumber' => "0000"];
        $this->mockedSmServices[Client::class]->shouldReceive('getCompanyProfile')
            ->with($queryData['companyNumber'])
            ->once()
            ->andThrow(NotFoundException::class);

        $query = (new \Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber())->create($queryData);
        $this->expectException(DomainNotFoundException::class);
        $actual = $this->sut->handleQuery($query);

        $expected = [
            'count' => 1,
            'result' => [['test']]
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testExpectedExceptionWhenCompanyServiceFails(): void
    {
        $queryData = ['companyNumber' => "12345678"];
        $this->mockedSmServices[Client::class]->shouldReceive('getCompanyProfile')
            ->with($queryData['companyNumber'])
            ->once()
            ->andThrow(ServiceException::class);

        $query = (new \Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber())->create($queryData);
        $this->expectException(\Exception::class);
        $this->sut->handleQuery($query);
    }

    public function testCompanyNumberFormat(): void
    {
        $queryData = ['companyNumber' => "1000"];
        $this->mockedSmServices[Client::class]->shouldReceive('getCompanyProfile')
            ->with("0000" . $queryData['companyNumber'])
            ->once()
            ->andReturn(['test']);

        $query = (new \Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber())->create($queryData);
        $actual = $this->sut->handleQuery($query);
        $expected = [
            'count' => 1,
            'result' => [['test']]
        ];
        $this->assertEquals($expected, $actual);
    }
}

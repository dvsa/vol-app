<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\CompaniesHouse\Service;

use Dvsa\Olcs\CompaniesHouse\Service\Client;
use Dvsa\Olcs\CompaniesHouse\Service\Exception;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\NotFoundException;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\RateLimitException;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\ServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response;

/**
 * @covers Dvsa\Olcs\CompaniesHouse\Service\Client
 */
class ClientTest extends MockeryTestCase
{
    public const COMPANY_NO = '03127414';

    /** @var  Client */
    protected $sut;

    /** @var  \Laminas\Http\Client|m\MockInterface */
    private $mockHttpClient;
    /** @var  m\MockInterface */
    private $mockRequest;

    public function setUp(): void
    {
        $this->mockRequest = m::mock(\Laminas\Http\Request::class)->makePartial();
        $this->mockRequest->shouldReceive('setMethod')->with('GET')->andReturnSelf();

        $this->mockHttpClient = m::mock(\Laminas\Http\Client::class)
            ->shouldReceive('getRequest')->with()->once()->andReturn($this->mockRequest)
            ->getMock();

        $this->sut = new Client();
        $this->sut->setBaseUri('BASE_URI');
        $this->sut->setHttpClient($this->mockHttpClient);
        $this->sut->setRateLimit(0);
    }

    public function testGetCompanyProfile(): void
    {
        $this->mockRequest
            ->shouldReceive('setUri')->with('BASE_URI/company/03127414')->once()->andReturnSelf();

        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_200);
        $response->setContent('{"company_number": "1234"}');

        $this->mockHttpClient->shouldReceive('send')->with()->once()->andReturn($response);

        static::assertEquals(
            ['company_number' => '1234'],
            $this->sut->getCompanyProfile(self::COMPANY_NO, false)
        );
    }

    public function testGetCompanyProfileFailInvalidJson(): void
    {
        //  expect
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage(Client::ERR_INVALID_JSON);

        //  call
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_200);
        $response->setContent('{"foo":"bar"}');

        $this->mockHttpClient
            ->shouldReceive('send')->with()->once()->andReturn($response);

        $this->sut->getCompanyProfile(self::COMPANY_NO, false);
    }

    public function testGetCompanyProfileWithOfficers(): void
    {
        $this->mockRequest
            ->shouldReceive('setUri')->once()->with('BASE_URI/company/03127414')->andReturnSelf()
            ->shouldReceive('setUri')->once()->with('BASE_URI/company/03127414/officers')->andReturnSelf();

        $companyResponse = new Response();
        $companyResponse->setStatusCode(Response::STATUS_CODE_200);
        $companyResponse->setContent('{"company_number": "bar"}');

        $officersResponse = new Response();
        $officersResponse->setStatusCode(Response::STATUS_CODE_200);
        $officersResponse->setContent('{"items": {"0":{"name":"Bob"}, "1":{"name":"Dave"}}}');

        $this->mockHttpClient
            ->shouldReceive('getRequest')->once()->andReturn($this->mockRequest)
            ->shouldReceive('send')->andReturn($companyResponse, $officersResponse);

        static::assertEquals(
            [
                'company_number' => 'bar',
                'officer_summary' => [
                    'officers' => [
                        ['name' => 'Bob'],
                        ['name' => 'Dave'],
                    ]
                ]
            ],
            $this->sut->getCompanyProfile(self::COMPANY_NO, true)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetCompanyProfileErrorResponse')]
    public function testGetCompanyProfileErrorResponse(mixed $statusCode, mixed $content, mixed $errClass, mixed $errMsg): void
    {
        //  expect
        $this->expectException($errClass);

        //  call
        $this->mockRequest->shouldReceive('setUri')->once()->with('BASE_URI/company/03127414')->andReturnSelf();

        $companyResponse = new Response();
        $companyResponse->setStatusCode($statusCode);
        $companyResponse->setContent($content);

        $this->mockHttpClient
            ->shouldReceive('send')
            ->andReturn($companyResponse);

        $this->sut->getCompanyProfile(self::COMPANY_NO);
    }

    public static function dpTestGetCompanyProfileErrorResponse(): array
    {
        return [
            [
                'statusCode' => Response::STATUS_CODE_404,
                'content' => '{"errors": [{"error": "not found"}]}',
                'errClass' => ServiceException::class,
                'errMsg' => Client::ERR_SERVICE_NOT_RESPOND,
            ],
            [
                'statusCode' => Response::STATUS_CODE_404,
                'content' => '{"errors": [{"error": "' . Client::ERR_KEY_COMPANY_PROFILE_NOT_FOUND . '"}]}',
                'errClass' => NotFoundException::class,
                'errMsg' => Client::ERR_COMPANY_PROFILE_NOT_FOUND,
            ],
            [
                'statusCode' => Response::STATUS_CODE_429,
                'content' => '',
                'errClass' => RateLimitException::class,
                'errMsg' => Client::ERR_RATE_LIMIT_EXCEED,
            ],
            [
                'statusCode' => Response::STATUS_CODE_500,
                'content' => '{"body": "test"}',
                'errClass' => ServiceException::class,
                'errMsg' => '{"body": "test"}',
            ],
            [
                'statusCode' => Response::STATUS_CODE_200,
                'content' => 'non-json content',
                'errClass' => ServiceException::class,
                'errMsg' => Client::ERR_INVALID_JSON,
            ],
            [
                'statusCode' => Response::STATUS_CODE_404,
                'content' => '{"errors":[{"type":"ch:service","error":"company-profile-not-found"}]}',
                'errClass' => NotFoundException::class,
                'errMsg' => Client::ERR_COMPANY_PROFILE_NOT_FOUND,
            ],
        ];
    }
}

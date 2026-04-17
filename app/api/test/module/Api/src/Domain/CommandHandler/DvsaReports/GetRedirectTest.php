<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DvsaReports;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Laminas\Http\Client;
use Laminas\Http\Response;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\DvsaReports\GetRedirect as GetRedirectHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\DvsaReports\GetRedirect as GetRedirectCmd;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use LmcRbacMvc\Service\AuthorizationService;
use ReflectionClass;

/**
 * Get DVSA Reports Redirect Test
 */
class GetRedirectTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var Client|(Client&LegacyMockInterface)|(Client&MockInterface)|LegacyMockInterface|MockInterface
     */
    protected $mockHttpClient;

    protected $config = [
        'top-report-link' => [
            'targetUrl' => 'apiurl',
            'apiKey' => '123',
            'proxy' => 'http://proxy:123'
        ]
    ];

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->mockHttpClient = m::mock(Client::class);
        $this->sut = new GetRedirectHandler($this->mockHttpClient);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        $this->mockedSmServices['config'] = [
            'top-report-link' => [
                'targetUrl' => 'apiurl',
                'apiKey' => '123',
                'proxy' => 'http://proxy:123'
            ]
        ];
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'olNumbers' => ['OL123456', 'OM456789'],
            'jwt' => 'jwt',
            'refreshToken' => 'rt'
        ];

        $command = GetRedirectCmd::create($cmdData);

        $identity = m::mock();

        /** @var Organisation $org */
        $org = m::mock(Organisation::class)->makePartial();
        $org->setName('SomeOperator');

        /** @var Organisation $org */
        $orgUser = m::mock(OrganisationUser::class)->makePartial();
        $orgUser->setOrganisation($org);

        /** @var User|m\Mock $user */
        $user = m::mock(User::class)->makePartial();
        $user->initCollections();
        $user->setOrganisationUsers(new ArrayCollection([$orgUser]));
        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_OPERATOR);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')->once()->with()->andReturn($identity);

        $identity->shouldReceive('getUser')->with()->once()->andReturn($user);

        $this->mockHttpClient->shouldReceive('setAdapter')->once()->andReturnSelf();
        $this->mockHttpClient->shouldReceive('setUri')->once()->with('apiurl')->andReturnSelf();
        $this->mockHttpClient->shouldReceive('setMethod')->once()->with('POST')->andReturnSelf();
        $this->mockHttpClient->shouldReceive('setRawBody')
            ->once()
            ->with('{"operators":["OL123456","OM456789"],"operator_name":"SomeOperator","refresh_token":"rt"}')
            ->andReturnSelf();
        $this->mockHttpClient->shouldReceive('setHeaders')->once()->with(
            [
                'Content-Type' => 'application/json',
                'x-api-key' => '123',
                'Authorization' => 'Bearer jwt'
            ]
        )->andReturnSelf();

        $apiResponse = m::mock(Response::class);
        $this->mockHttpClient->shouldReceive('send')->once()->withNoArgs()->andReturn($apiResponse);
        $apiResponse->shouldReceive('getContent')->once()->andReturn('{"redirectUrl": "somenewurl"}');

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ["somenewurl"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\SaveBusinessDetails;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Licence\SaveBusinessDetails
 */
class SaveBusinessDetailsTest extends AbstractCommandHandlerTestCase
{
    public const ID = 1111;
    public const VERSION = 99;
    public const CONTACT_DETAILS_ID = 8888;

    /** @var  SaveBusinessDetails */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockAuthSrv;

    /** @var  m\MockInterface */
    private $mockOrgEntity;
    /** @var  m\MockInterface */
    private $mockLicenceEntity;

    public function setUp(): void
    {
        $this->sut = new SaveBusinessDetails();

        //  mock entities
        $this->mockOrgEntity = m::mock(Entity\Organisation\Organisation::class)->makePartial();
        $this->mockLicenceEntity = m::mock(Entity\Licence\Licence::class)->makePartial()
            ->shouldReceive('getOrganisation')->once()->andReturn($this->mockOrgEntity)
            ->shouldReceive('getId')->andReturn(self::ID)
            ->getMock();

        //  mock repositories
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('Organisation', Repository\Organisation::class);

        $this->repoMap['Organisation']
            ->shouldReceive('lock')->with($this->mockOrgEntity, self::VERSION)->once()->andReturnSelf()
            ->shouldReceive('save')->with($this->mockOrgEntity)->atMost()->andReturnSelf();

        //  mock services
        $this->mockAuthSrv = m::mock(AuthorizationService::class);
        $this->mockedSmServices[AuthorizationService::class] = $this->mockAuthSrv;

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandlerExceptionNotAllow')]
    public function testHandlerExceptionNotAllow(mixed $orgData): void
    {
        $data = [
            'id' => self::ID,
            'name' => 'unit_OrgName',
            'companyOrLlpNo' => '123456798',
            'version' => self::VERSION,
        ];
        $command = DomainCmd\Licence\SaveBusinessDetails::create($data);

        //  mock organisation entity
        $this->mockOrgEntity
            ->shouldReceive('hasInforceLicences')->once()->andReturn(true)
            ->shouldReceive('getName')->once()->andReturn($orgData['name'])
            ->shouldReceive('getCompanyOrLlpNo')->andReturn($orgData['companyNo']);

        //  mock licence repo
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($this->mockLicenceEntity);

        //  mock permission check
        $this->mockIsGranted(Permission::INTERNAL_USER, false);

        //  expect
        $this->expectException(ForbiddenException::class);

        //  call
        $this->sut->handleCommand($command);
    }

    public static function dpTestHandlerExceptionNotAllow(): array
    {
        return [
            [
                'orgData' => [
                    'name' => 'change me',
                    'companyNo' => null,
                ],
            ],
            [
                'orgData' => [
                    'name' => 'unit_OrgName',
                    'companyNo' => 'change me',
                ],
            ],
        ];
    }

    public function testHandlerDetailsChanged(): void
    {
        //  mock permission check
        $this->mockIsGranted(Permission::INTERNAL_USER, true);

        $this->expectedLicenceCacheClearSideEffect(self::ID);

        $data = [
            'id' => self::ID,
            'name' => 'unit_OrgName',
            'companyOrLlpNo' => '123456798',
            'allowEmail' => 'Yes',
            'tradingNames' => [
                'unit_TradNameA',
                'unit_TradNameB'
            ],
            'registeredAddress' => [
                'addressLine1' => 'unit_Address_1',
                'postcode' => 'unit_AB1_9WQ'
            ],
            'natureOfBusiness' => 'unit_NoB',
            'version' => self::VERSION,
        ];
        $command = DomainCmd\Licence\SaveBusinessDetails::create($data);

        // mock handle registered address command
        $expectedData = [
            'addressLine1' => 'unit_Address_1',
            'postcode' => 'unit_AB1_9WQ',
        ];
        $cmdResult = new Result();
        $cmdResult->addId('contactDetails', self::CONTACT_DETAILS_ID);
        $cmdResult->setFlag('hasChanged', true);
        $cmdResult->addMessage('Address updated');

        $this->expectedSideEffect(DomainCmd\ContactDetails\SaveAddress::class, $expectedData, $cmdResult);

        // mock handle trading names command
        $expectedData = [
            'licence' => self::ID,
            'tradingNames' => [
                'unit_TradNameA',
                'unit_TradNameB',
            ],
        ];
        $cmdResult = new Result();
        $cmdResult->setFlag('hasChanged', true);
        $cmdResult->addMessage('Trading names updated');

        $this->expectedSideEffect(DomainCmd\Organisation\UpdateTradingNames::class, $expectedData, $cmdResult);

        //  mock organisation entity
        $this->mockOrgEntity
            ->shouldReceive('getVersion')->once()->andReturn(self::VERSION + 1)
            ->shouldReceive('setName')->once()->with('unit_OrgName')
            ->shouldReceive('setCompanyOrLlpNo')->once()->with('123456798')
            ->shouldReceive('setAllowEmail')->once()->with('Yes')
            ->shouldReceive('setNatureOfBusiness')->with('unit_NoB')->once()
            ->shouldReceive('setContactDetails')->once()->andReturnUsing(
                function () {
                    m::mock(Repository\Licence::class)
                        ->shouldReceive('getReference')
                        ->with(Entity\ContactDetails\ContactDetails::class, self::CONTACT_DETAILS_ID)
                        ->getMock();
                }
            );

        //  mock licence repo
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($this->mockLicenceEntity);

        //  call
        $actual = $this->sut->handleCommand($command);

        static::assertEquals(
            [
                'id' => [
                    'contactDetails' => self::CONTACT_DETAILS_ID,
                ],
                'messages' => [
                    'Address updated',
                    'Trading names updated',
                ],
                'flags' => ['hasChanged' => 1, 'tradingNamesChanged' => 1]
            ],
            $actual->toArray()
        );
    }

    public function testHandlerDetailsNotChanged(): void
    {
        //  mock permission check
        $this->mockIsGranted(Permission::INTERNAL_USER, true);

        $this->expectedLicenceCacheClearSideEffect(self::ID);

        $data = [
            'id' => self::ID,
            'version' => self::VERSION,
        ];
        $command = DomainCmd\Licence\SaveBusinessDetails::create($data);

        // mock handle trading names command
        $expectedData = [
            'licence' => self::ID,
            'tradingNames' => [],
        ];
        $cmdResult = new Result();
        $cmdResult->setFlag('hasChanged', false);
        $cmdResult->addMessage('Trading names are not changed');

        $this->expectedSideEffect(DomainCmd\Organisation\UpdateTradingNames::class, $expectedData, $cmdResult);

        //  mock organisation entity
        $this->mockOrgEntity
            ->shouldReceive('getVersion')->once()->andReturn(self::VERSION)
            ->shouldReceive('setNatureOfBusiness')->once()->with('')
            ->shouldReceive('setContactDetails')->never();

        //  mock licence repo
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($this->mockLicenceEntity);

        //  call
        $actual = $this->sut->handleCommand($command);

        static::assertEquals(
            [
                'id' => [],
                'messages' => [
                    'Trading names are not changed',
                ],
                'flags' => ['hasChanged' => false]
            ],
            $actual->toArray()
        );
        static::assertFalse($actual->getFlag('hasChanged'));
    }

    private function mockIsGranted(mixed $permission, mixed $result): void
    {
        $this->mockAuthSrv
            ->shouldReceive('isGranted')
            ->with($permission, null)
            ->andReturn($result);
    }
}

<?php

declare(strict_types=1);

/**
 * Create TmQualification Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmQualification;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TmQualification\Create as CreateTmQualification;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TmQualification\Create as Cmd;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification as TmQualificationEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Create TmQualification Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateTmQualification();
        $this->mockRepo('TmQualification', TmQualificationRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'qtype'
        ];

        $this->references = [
            CountryEntity::class => [
                1 => m::mock(CountryEntity::class)
            ],
            TransportManagerEntity::class => [
                2 => m::mock(TransportManagerEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $this->mockAuthService();

        $result = new Result();
        $result->addId('TmQualification', 1);
        $result->addMessage('TmQualification created successfully');

        $command = Cmd::create(
            [
                'qualificationType' => 'qtype',
                'serialNo'          => '123',
                'issuedDate'        => '2015-01-01',
                'countryCode'       => 'GB',
                'transportManager'  => 1
            ]
        );

        $tmQualification = null;

        $this->repoMap['TmQualification']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(TmQualificationEntity::class))
            ->andReturnUsing(
                function (TmQualificationEntity $tmq) use (&$tmQualification) {
                    $tmq->setId(111);
                    $tmQualification = $tmq;
                }
            );

        $res = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                    'TmQualification' => 111
                ],
                'messages' => [
                    'TmQualification created successfully'
                ]
            ],
            $res->toArray()
        );
    }

    protected function mockAuthService(): void
    {
        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);
    }
}

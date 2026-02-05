<?php

declare(strict_types=1);

/**
 * Update Conviction Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Conviction;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Conviction\Update as UpdateCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Conviction;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Cases\Conviction as ConvictionEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Update as UpdateCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Conviction Test
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var UpdateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new UpdateCommandHandler();
        $this->mockRepo('Conviction', Conviction::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'conv_c_cat_1',
            'def_t_dir',
        ];

        $this->references = [
            Entity\Tm\TransportManager::class => [
                55 => m::mock(Entity\Tm\TransportManager::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 150;
        $version = 2;

        $data = [
            'id' => $id,
            'version' => $version,
            'defendantType' => 'def_t_dir',
            'convictionCategory' => 'conv_c_cat_1',
            'categoryText' => 'cat text',
            'transportManager' => 55,
            'offenceDate' => '2014-01-01',
            'convictionDate' => '2014-01-02',
            'msi' => 'Y',
            'isDeclared' => 'Y',
            'isDealtWith' => 'N',
            'personFirstName' => 'Craig',
            'personLastName' => 'PA',
            'birthDate' => '1980-01-02',
            'court' => 'court',
            'penalty' => 'penalty',
            'costs' => 'costs',
            'notes' => 'notes',
            'takenIntoConsideration' => 'Y',
        ];

        $command = UpdateCommand::create($data);

        /** @var $conviction ConvictionEntity */
        $conviction = m::mock(ConvictionEntity::class);
        $conviction
            ->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($id)
            ->shouldReceive('setDefendantType')
            ->with($this->refData['def_t_dir'])
            ->once()
            ->shouldReceive('updateConvictionCategory')
            ->with($this->refData['conv_c_cat_1'], $data['categoryText'])
            ->once()
            ->shouldReceive('setTransportManager')
            ->with($this->references[Entity\Tm\TransportManager::class][55])
            ->once()
            ->shouldReceive('setPersonFirstName')
            ->with($data['personFirstName'])
            ->once()
            ->shouldReceive('setPersonLastname')
            ->with($data['personLastName'])
            ->once()
            ->shouldReceive('setBirthDate')
            ->with(m::type(DateTime::class))
            ->once()
            ->shouldReceive('setOffenceDate')
            ->with(m::type(\DateTime::class))
            ->once()
            ->shouldReceive('setConvictionDate')
            ->with(m::type(\DateTime::class))
            ->once()
            ->shouldReceive('setMsi')
            ->with($data['msi'])
            ->once()
            ->shouldReceive('setCourt')
            ->with($data['court'])
            ->once()
            ->shouldReceive('setPenalty')
            ->with($data['penalty'])
            ->once()
            ->shouldReceive('setCosts')
            ->with($data['costs'])
            ->once()
            ->shouldReceive('setNotes')
            ->with($data['notes'])
            ->once()
            ->shouldReceive('setTakenIntoConsideration')
            ->with($data['takenIntoConsideration'])
            ->once()
            ->shouldReceive('setIsDeclared')
            ->with($data['isDeclared'])
            ->once()
            ->shouldReceive('setIsDealtWith')
            ->with($data['isDealtWith'])
            ->once();

        $this->repoMap['Conviction']
            ->shouldReceive('fetchById')
            ->with($id, Query::HYDRATE_OBJECT, $version)
            ->once()
            ->andReturn($conviction)
            ->shouldReceive('save')
            ->with($conviction)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'conviction' => $id,
            ],
            'messages' => [
                'Conviction Updated'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testHandleCommandWithoutConvictionCategory(): void
    {
        $id = 150;
        $version = 2;

        $data = [
            'id' => $id,
            'version' => $version,
            'defendantType' => 'def_t_dir',
            'convictionCategory' => '',
            'offenceDate' => '2014-01-01',
            'convictionDate' => '2014-01-02',
            'msi' => 'Y',
            'isDeclared' => 'Y',
            'isDealtWith' => 'N',
        ];

        $command = UpdateCommand::create($data);

        /** @var $conviction ConvictionEntity */
        $conviction = m::mock(ConvictionEntity::class);
        $conviction
            ->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($id)
            ->shouldReceive('setDefendantType')
            ->with($this->refData['def_t_dir'])
            ->once()
            ->shouldReceive('updateConvictionCategory')
            ->with(null, null)
            ->once()
            ->shouldReceive('setOffenceDate')
            ->with(m::type(\DateTime::class))
            ->once()
            ->shouldReceive('setConvictionDate')
            ->with(m::type(\DateTime::class))
            ->once()
            ->shouldReceive('setMsi')
            ->with($data['msi'])
            ->once()
            ->shouldReceive('setIsDeclared')
            ->with($data['isDeclared'])
            ->once()
            ->shouldReceive('setIsDealtWith')
            ->with($data['isDealtWith'])
            ->once();

        $this->repoMap['Conviction']
            ->shouldReceive('fetchById')
            ->with($id, Query::HYDRATE_OBJECT, $version)
            ->once()
            ->andReturn($conviction)
            ->shouldReceive('save')
            ->with($conviction)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'conviction' => $id,
            ],
            'messages' => [
                'Conviction Updated'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }
}

<?php

declare(strict_types=1);

/**
 * Create Conviction Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Conviction;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Conviction\Create as CreateCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Conviction;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Cases\Conviction as ConvictionEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Create as CreateCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Conviction Test
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var CreateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CreateCommandHandler();
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
            Entity\Cases\Cases::class => [
                50 => m::mock(Entity\Cases\Cases::class)
            ],
            Entity\Tm\TransportManager::class => [
                55 => m::mock(Entity\Tm\TransportManager::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'defendantType' => 'def_t_dir',
            'convictionCategory' => 'conv_c_cat_1',
            'categoryText' => 'cat text',
            'transportManager' => 55,
            'case' => 50,
            'personFirstName' => 'Craig',
            'personLastName' => 'PA',
            'birthDate' => '1980-01-02',
            'offenceDate' => '2014-01-01',
            'convictionDate' => '2014-01-02',
            'msi' => 'Y',
            'court' => 'court',
            'penalty' => 'penalty',
            'costs' => 'costs',
            'notes' => 'notes',
            'takenIntoConsideration' => 'Y',
            'isDeclared' => 'Y',
            'isDealtWith' => 'N',
        ];

        $command = CreateCommand::create($data);

        /** @var $conv ConvictionEntity */
        $conv = null;

        $this->repoMap['Conviction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(ConvictionEntity::class))
            ->andReturnUsing(
                function (ConvictionEntity $entity) use (&$conv) {
                    $entity->setId(111);
                    $conv = $entity;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'conviction' => 111,
            ],
            'messages' => [
                'Conviction Created'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());

        $this->assertEquals(111, $conv->getId());
        $this->assertSame($this->refData['def_t_dir'], $conv->getDefendantType());
        $this->assertSame($this->refData['conv_c_cat_1'], $conv->getConvictionCategory());
        $this->assertEquals($data['categoryText'], $conv->getCategoryText());
        $this->assertSame($this->references[Entity\Tm\TransportManager::class][55], $conv->getTransportManager());
        $this->assertSame($this->references[Entity\Cases\Cases::class][50], $conv->getCase());
        $this->assertEquals($data['personFirstName'], $conv->getPersonFirstname());
        $this->assertEquals($data['personLastName'], $conv->getPersonLastName());
        $this->assertEquals($data['birthDate'], $conv->getBirthDate()->format('Y-m-d'));
        $this->assertEquals($data['offenceDate'], $conv->getOffenceDate()->format('Y-m-d'));
        $this->assertEquals($data['convictionDate'], $conv->getConvictionDate()->format('Y-m-d'));
        $this->assertEquals($data['msi'], $conv->getMsi());
        $this->assertEquals($data['court'], $conv->getCourt());
        $this->assertEquals($data['penalty'], $conv->getPenalty());
        $this->assertEquals($data['costs'], $conv->getCosts());
        $this->assertEquals($data['notes'], $conv->getNotes());
        $this->assertEquals($data['takenIntoConsideration'], $conv->getTakenIntoConsideration());
        $this->assertEquals($data['isDeclared'], $conv->getIsDeclared());
        $this->assertEquals($data['isDealtWith'], $conv->getIsDealtWith());
    }

    public function testHandleCommandWithoutOptional(): void
    {
        $data = [
            'defendantType' => 'def_t_dir',
            'convictionCategory' => '',
            'categoryText' => 'cat text',
            'offenceDate' => '2014-01-01',
            'convictionDate' => '2014-01-02',
            'msi' => 'Y',
            'isDeclared' => 'Y',
            'isDealtWith' => 'N',
        ];

        $command = CreateCommand::create($data);

        /** @var $conv ConvictionEntity */
        $conv = null;

        $this->repoMap['Conviction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(ConvictionEntity::class))
            ->andReturnUsing(
                function (ConvictionEntity $entity) use (&$conv) {
                    $entity->setId(111);
                    $conv = $entity;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'conviction' => 111,
            ],
            'messages' => [
                'Conviction Created'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());

        $this->assertEquals(111, $conv->getId());
        $this->assertEquals($this->refData['def_t_dir'], $conv->getDefendantType());
        $this->assertNull($conv->getConvictionCategory());
        $this->assertEquals($data['categoryText'], $conv->getCategoryText());
        $this->assertNull($conv->getTransportManager());
        $this->assertNull($conv->getCase());
        $this->assertNull($conv->getPersonFirstname());
        $this->assertNull($conv->getPersonLastName());
        $this->assertNull($conv->getBirthDate());
        $this->assertEquals($data['offenceDate'], $conv->getOffenceDate()->format('Y-m-d'));
        $this->assertEquals($data['convictionDate'], $conv->getConvictionDate()->format('Y-m-d'));
        $this->assertEquals($data['msi'], $conv->getMsi());
        $this->assertNull($conv->getCourt());
        $this->assertNull($conv->getPenalty());
        $this->assertNull($conv->getCosts());
        $this->assertNull($conv->getNotes());
        $this->assertNull($conv->getTakenIntoConsideration());
        $this->assertEquals($data['isDeclared'], $conv->getIsDeclared());
        $this->assertEquals($data['isDealtWith'], $conv->getIsDealtWith());
    }
}

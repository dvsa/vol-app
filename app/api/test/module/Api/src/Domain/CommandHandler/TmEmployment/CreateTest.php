<?php

declare(strict_types=1);

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\CommandHandler\TmEmployment\Create as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\TmEmployment\Create as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TmEmployment', \Dvsa\Olcs\Api\Domain\Repository\TmEmployment::class);
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails::class => [
                837 => m::mock(\Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails::class)
            ],
            \Dvsa\Olcs\Api\Entity\Tm\TransportManager::class => [
                1171 => m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandTma(): void
    {
        $command = Command::create(
            [
                'tmaId' => 172,
                'position' => 'POSITION',
                'hoursPerWeek' => 54,
                'employerName' => 'ACME Ltd',
                'address' => [
                    'addressLine1' => 'LINE 1',
                    'addressLine2' => 'LINE 2',
                    'addressLine3' => 'LINE 3',
                    'addressLine4' => 'LINE 4',
                    'town' => 'TOWN',
                    'postcode' => 'S1 4QT',
                    'countryCode' => 'CC',
                ],
            ]
        );

        $tma = new TransportManagerApplication();
        $tma->setTransportManager('TRANSPORT_MANAGER');

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchById')->with(172)->once()->andReturn($tma);

        $sideEffectResult = new \Dvsa\Olcs\Api\Domain\Command\Result();
        $sideEffectResult->addId('contactDetails', 837);
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress::class,
            [
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
                'contactType' => 'ct_tm',
                'version' => null,
                'id' => null,
            ],
            $sideEffectResult
        );

        $this->repoMap['TmEmployment']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\Tm\TmEmployment $tme) {
                    $tme->setId(648);
                    $this->assertSame('TRANSPORT_MANAGER', $tme->getTransportManager());
                    $this->assertSame('POSITION', $tme->getPosition());
                    $this->assertSame(54, $tme->getHoursPerWeek());
                    $this->assertSame('ACME Ltd', $tme->getEmployerName());
                    $this->assertSame(
                        $this->references[\Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails::class][837],
                        $tme->getContactDetails()
                    );
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['contactDetails' => 837, 'tmEmployment' => 648], $response->getIds());
        $this->assertSame(['Tm Employment ID 648 created'], $response->getMessages());
    }

    public function testHandleCommandTm(): void
    {
        $command = Command::create(
            [
                'transportManager' => 1171,
                'position' => 'POSITION',
                'hoursPerWeek' => 54,
                'employerName' => 'ACME Ltd',
                'address' => [
                    'addressLine1' => 'LINE 1',
                    'addressLine2' => 'LINE 2',
                    'addressLine3' => 'LINE 3',
                    'addressLine4' => 'LINE 4',
                    'town' => 'TOWN',
                    'postcode' => 'S1 4QT',
                    'countryCode' => 'CC',
                ],
            ]
        );

        $sideEffectResult = new \Dvsa\Olcs\Api\Domain\Command\Result();
        $sideEffectResult->addId('contactDetails', 837);
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress::class,
            [
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
                'contactType' => 'ct_tm',
                'version' => null,
                'id' => null,
            ],
            $sideEffectResult
        );

        $this->repoMap['TmEmployment']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\Tm\TmEmployment $tme) {
                    $tme->setId(648);
                    $this->assertSame(
                        $this->references[\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class][1171],
                        $tme->getTransportManager()
                    );
                    $this->assertSame('POSITION', $tme->getPosition());
                    $this->assertSame(54, $tme->getHoursPerWeek());
                    $this->assertSame('ACME Ltd', $tme->getEmployerName());
                    $this->assertSame(
                        $this->references[\Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails::class][837],
                        $tme->getContactDetails()
                    );
                }
            );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['contactDetails' => 837, 'tmEmployment' => 648], $response->getIds());
        $this->assertSame(['Tm Employment ID 648 created'], $response->getMessages());
    }

    public function testHandleCommandMissingTmAndTma(): void
    {
        $command = Command::create(
            [
                'transportManager' => 'X',
                'position' => 'POSITION',
                'hoursPerWeek' => 54,
                'employerName' => 'ACME Ltd',
                'address' => [
                    'addressLine1' => 'LINE 1',
                    'addressLine2' => 'LINE 2',
                    'addressLine3' => 'LINE 3',
                    'addressLine4' => 'LINE 4',
                    'town' => 'TOWN',
                    'postcode' => 'S1 4QT',
                    'countryCode' => 'CC',
                ],
            ]
        );

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand($command);
    }
}

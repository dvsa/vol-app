<?php

declare(strict_types=1);

/**
 * UpdateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\CommandHandler\TmEmployment\Update as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\TmEmployment\Update as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
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
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(
            [
                'id' => 172,
                'version' => 326,
                'position' => 'POSITION',
                'hoursPerWeek' => 54,
                'employerName' => 'ACME Ltd',
                'address' => [
                    'version' => 432,
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

        $tmEmployment = new \Dvsa\Olcs\Api\Entity\Tm\TmEmployment();
        $tmEmployment->setId(172);
        $contactDetails = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $address = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();

        $tmEmployment->setContactDetails($contactDetails);
        $contactDetails->setAddress($address);
        $address->setId(44);

        $this->repoMap['TmEmployment']->shouldReceive('fetchUsingId')
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 326)->once()->andReturn($tmEmployment);

        $this->repoMap['TmEmployment']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Tm\TmEmployment $tme) {
                $this->assertSame('POSITION', $tme->getPosition());
                $this->assertSame(54, $tme->getHoursPerWeek());
                $this->assertSame('ACME Ltd', $tme->getEmployerName());
            }
        );

        $sideEffectResult = new \Dvsa\Olcs\Api\Domain\Command\Result();
        $sideEffectResult->addId('contactDetails', 837);
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress::class,
            [
                'id' => 44,
                'version' => 432,
                'addressLine1' => 'LINE 1',
                'addressLine2' => 'LINE 2',
                'addressLine3' => 'LINE 3',
                'addressLine4' => 'LINE 4',
                'town' => 'TOWN',
                'postcode' => 'S1 4QT',
                'countryCode' => 'CC',
                'contactType' => null,
            ],
            $sideEffectResult
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['Tm Employment ID 172 updated'], $response->getMessages());
    }
}

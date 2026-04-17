<?php

declare(strict_types=1);

/**
 * SendTmApplicationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendTmApplication as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\SendTmApplication as Cmd;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;

/**
 * SendTmApplicationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class SendTmApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class)
        ];

        parent::setUp();
    }

    public static function dataProviderTestHandleCommand(): array
    {
        return [
            [0, 'application'],
            [1, 'variation']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestHandleCommand')]
    public function testHandleCommand(mixed $isVariation, mixed $uriPart): void
    {
        $command = Cmd::create(['id' => 863]);

        $tm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $hcd = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(m::mock(RefData::class));
        $tm->setHomeCd($hcd)->getHomeCd()->setEmailAddress('test@email.com');

        $cd = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(m::mock(RefData::class));
        $cd->setEmailAddress('EMAIL');

        $user = new \Dvsa\Olcs\Api\Entity\User\User('pid', 'TYPE');
        $user->setLoginId('username1');
        $user->setContactDetails($cd);
        $user->setTranslateToWelsh('Y');
        $tm->addUsers($user);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setName('ORGANISATION');
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, m::mock(RefData::class));
        $licence->setLicNo('LIC01');
        $licence->setTranslateToWelsh('N');
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            m::mock(RefData::class),
            $isVariation
        );
        $application->setId(442);
        $tma = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma->setTransportManager($tm);
        $tma->setApplication($application);
        $tma->setId(75);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($tma);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            'transport-manager-complete-digital-form',
            [
                'organisation' => 'ORGANISATION',
                'reference' => 'LIC01/442',
                'username' => 'username1',
                'isNi' => false,
                'signInLink' => 'http://selfserve/' . $uriPart . '/442/transport-managers/details/75/edit-details/'
            ],
            'default'
        );

        $result = new Result();
        $data = [
            'to' => 'test@email.com',
            'locale' => 'cy_GB',
            'subject' => 'email.transport-manager-complete-digital-form.subject'
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['transportManagerApplication' => 75], $result->getIds());
        $this->assertSame(['Transport Manager Application email sent'], $result->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestHandleCommand')]
    public function testHandleCommandWithoutTmUsers(mixed $isVariation, mixed $uriPart): void
    {
        $command = Cmd::create(['id' => 863]);

        $hcd = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(m::mock(RefData::class));
        $hcd->setEmailAddress('test@123.com');
        $tm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $tm->setHomeCd($hcd);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setName('ORGANISATION');
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, m::mock(RefData::class));
        $licence->setLicNo('LIC01');
        $licence->setTranslateToWelsh('N');
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            m::mock(RefData::class),
            $isVariation
        );
        $application->setId(442);
        $tma = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication();
        $tma->setTransportManager($tm);
        $tma->setApplication($application);
        $tma->setId(75);

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($tma);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            'transport-manager-complete-digital-form',
            [
                'organisation' => 'ORGANISATION',
                'reference' => 'LIC01/442',
                'username' => 'not registered',
                'isNi' => false,
                'signInLink' => 'http://selfserve/' . $uriPart . '/442/transport-managers/details/75/edit-details/'
            ],
            'default'
        );

        $result = new Result();
        $data = [
            'to' => 'test@123.com',
            'locale' => 'en_GB',
            'subject' => 'email.transport-manager-complete-digital-form.subject'
        ];

        $this->expectedSideEffect(SendEmail::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['transportManagerApplication' => 75], $result->getIds());
        $this->assertSame(['Transport Manager Application email sent'], $result->getMessages());
    }
}

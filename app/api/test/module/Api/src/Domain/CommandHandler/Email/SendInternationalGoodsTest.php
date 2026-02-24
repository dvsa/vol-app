<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Email\SendInternationalGoods as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendInternationalGoods;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Mockery as m;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;

/**
 * Send Psv Operator List Report Test
 */
class SendInternationalGoodsTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var CommandInterface|SendInternationalGoods
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new SendInternationalGoods();
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 999;
        $command = Cmd::create(['id' => $id]);
        $email = 'toemail@email.com';
        $ccList = 'ccemail1@email.com, ccemail2@email.com';

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::INTERNATIONAL_GV_REPORT_EMAIL_TO)
            ->andReturn($email);

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::INTERNATIONAL_GV_REPORT_EMAIL_CC)
            ->andReturn($ccList);

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(Message::class),
            SendInternationalGoods::EMAIL_TEMPLATE,
            [],
            'default'
        );

        $this->expectedSideEffect(
            SendEmail::class,
            [
                'to' => 'toemail@email.com',
                'cc' => [
                    'ccemail1@email.com',
                    'ccemail2@email.com'
                ]
            ],
            new Result()
        );

        /** @var Result $result */
        $result = $this->sut->handleCommand($command);

        $this->assertSame($id, $result->getId('document'));
        $this->assertSame(['International goods sent to: ' . $email . ' and CC to ' . $ccList], $result->getMessages());
    }

    public function testNoEmailThrowException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );

        $command = Cmd::create(['id' => 999]);

        // expectations
        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::INTERNATIONAL_GV_REPORT_EMAIL_TO)
            ->andReturnNull();

        $this->sut->handleCommand($command);
    }
}

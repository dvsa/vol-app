<?php

namespace CommonTest\Service\User;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\User\LastLoginService;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserLastLoginAt;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class LastLoginServiceTest
 * @package CommonTest\Service\User
 */
class LastLoginServiceTest extends MockeryTestCase
{
    public const TOKEN = "exampleToken";

    /** @var LastLoginService */
    private $sut;

    /**
     * @var CommandSender|m\LegacyMockInterface|m\MockInterface
     */
    private $commandSender;

    #[\Override]
    protected function setUp(): void
    {
        $this->commandSender = m::mock(CommandSender::class);
        $this->sut = new LastLoginService($this->commandSender);
    }

    public function testCommandIsInstantiatedWithToken(): void
    {
        $this->commandSender
            ->shouldReceive('send')
            ->with(
                m::on(function ($command) {
                    $this->assertInstanceOf(UpdateUserLastLoginAt::class, $command);
                    $this->assertEquals($command->getSecureToken(), self::TOKEN);
                    return true;
                })
            );

        $this->sut->updateLastLogin(self::TOKEN);
    }
}

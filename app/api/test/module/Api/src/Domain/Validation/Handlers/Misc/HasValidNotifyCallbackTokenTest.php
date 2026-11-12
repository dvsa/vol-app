<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\HasValidNotifyCallbackToken;
use Dvsa\Olcs\Transfer\Command\Notify\UpdateDeliveryStatus;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(HasValidNotifyCallbackToken::class)]
final class HasValidNotifyCallbackTokenTest extends AbstractHandlerTestCase
{
    private const string TOKEN = 'a-long-secret-token';

    public function setUp(): void
    {
        $this->sut = new HasValidNotifyCallbackToken();

        parent::setUp();
    }

    private function dto(?string $token, bool $read = true): UpdateDeliveryStatus
    {
        $dto = m::mock(UpdateDeliveryStatus::class);
        $expectation = $dto->shouldReceive('getToken')->withNoArgs();
        $read ? $expectation->once()->andReturn($token) : $expectation->never();

        return $dto;
    }

    private function sut(string $configuredToken): HasValidNotifyCallbackToken
    {
        $this->sut->setExpectedToken($configuredToken);

        return $this->sut;
    }

    public function testRightToken(): void
    {
        $this->assertTrue($this->sut(self::TOKEN)->isValid($this->dto(self::TOKEN)));
    }

    public function testRightTokenWithTrailingNewlineInConfig(): void
    {
        $this->assertTrue($this->sut(self::TOKEN . "\n")->isValid($this->dto(self::TOKEN)));
    }

    public function testWrongToken(): void
    {
        $this->assertFalse($this->sut(self::TOKEN)->isValid($this->dto('wrong')));
    }

    public function testMissingToken(): void
    {
        $this->assertFalse($this->sut(self::TOKEN)->isValid($this->dto(null)));
    }

    #[DataProvider('unsetProvider')]
    public function testUnsetConfigRejectsEverything(string $configuredToken, ?string $token): void
    {
        $this->assertFalse($this->sut($configuredToken)->isValid($this->dto($token, false)));
    }

    public static function unsetProvider(): array
    {
        return [
            'blank' => ['', ''],
            'space' => [' ', ''],
            'null string' => ['null', 'null'],
            'blank with missing token' => ['', null],
        ];
    }

    public function testTokenIsReadFromConfig(): void
    {
        $container = m::mock(\Psr\Container\ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->once()->andReturn(['notify' => ['callback_token' => self::TOKEN]]);
        $container->shouldReceive('get')->with('DomainValidatorManager')->once()->andReturn(m::mock(\Dvsa\Olcs\Api\Domain\ValidatorManager::class));

        $sut = new HasValidNotifyCallbackToken();
        $sut($container, HasValidNotifyCallbackToken::class);

        $this->assertTrue($sut->isValid($this->dto(self::TOKEN)));
    }
}

<?php

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\HidePassword;
use PHPUnit\Framework\TestCase;

class HidePasswordTest extends TestCase
{
    public function testProcessRedactsPasswordsInContext(): void
    {
        $context = [
            'foo' => 'bar',
            'some' => 'asdaspaSSworddasd',
            'some1' => 'thing',
            'password' => 'another-password',
            'something' => [
                'somethingelse' => [
                    'foo' => 'bar',
                    'passWORD' => 'secret',
                    'content' => 'asdaspassworddasd',
                    'foo2' => 'bar2',
                ],
            ],
            'foo2' => 'bar2',
            'cognitoPass' => '"{\"file\":\"/opt/dvsa/olcs/api/module/Auth/src/Adapter/CognitoAdapter.php\",\"line\":53}"',
        ];

        $sut = new HidePassword();
        $record = new LogRecord(new DateTimeImmutable(), 'test', Level::Info, '', $context);

        $result = $sut($record);

        $this->assertSame(
            [
                'foo' => 'bar',
                'some' => '*** HIDDEN PASSWORD ***',
                'some1' => 'thing',
                'password' => '*** HIDDEN PASSWORD ***',
                'something' => [
                    'somethingelse' => [
                        'foo' => 'bar',
                        'passWORD' => '*** HIDDEN PASSWORD ***',
                        'content' => '*** HIDDEN PASSWORD ***',
                        'foo2' => 'bar2',
                    ],
                ],
                'foo2' => 'bar2',
                'cognitoPass' => '*** HIDDEN PASSWORD ***',
            ],
            $result->context
        );
    }

    public function testProcessRedactsPasswordsInExtra(): void
    {
        $extra = ['password' => 'leaked', 'safe' => 'ok'];

        $sut = new HidePassword();
        $record = new LogRecord(new DateTimeImmutable(), 'test', Level::Info, '', [], $extra);

        $result = $sut($record);

        $this->assertSame(
            ['password' => '*** HIDDEN PASSWORD ***', 'safe' => 'ok'],
            $result->extra
        );
    }
}

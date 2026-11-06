<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use Dvsa\Olcs\Email\Transport\NotifyErrors;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Dvsa\Olcs\Email\Service\NotifyClientFactory;
use Dvsa\Olcs\Email\Service\NotifyClientFactoryFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

final class NotifyClientFactoryTest extends TestCase
{
    public function testFromApiKeyReturnsNotifyClient(): void
    {
        $key = 'test-11111111-1111-4111-8111-111111111111-22222222-2222-4222-8222-222222222222';
        $this->assertInstanceOf(NotifyClient::class, (new NotifyClientFactory())->fromApiKey($key));
    }

    private const string KEY = 'test-11111111-1111-4111-8111-111111111111-22222222-2222-4222-8222-222222222222';

    private function clientReturning(Response $response): NotifyClient
    {
        $handler = HandlerStack::create(new MockHandler([$response]));

        return (new NotifyClientFactory(['handler' => $handler]))->fromApiKey(self::KEY);
    }

    public function testNotifyErrorKeepsNotifysOwnMessage(): void
    {
        $body = json_encode([
            'errors' => [['error' => 'BadRequestError', 'message' => "Can't send to this recipient using a team-only API key"]],
            'status_code' => 400,
        ]);

        try {
            $this->clientReturning(new Response(400, ['Content-Type' => 'application/json'], $body))
                ->sendSms('07700900321', 'tpl', ['body' => 'x'], 'dev-sms-1');
            $this->fail('expected a NotifyException');
        } catch (NotifyException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame('BadRequestError: "Can\'t send to this recipient using a team-only API key"', NotifyErrors::message($e));
        }
    }

    public function testGatewayPageFallsBackToTheHttpError(): void
    {
        try {
            $this->clientReturning(new Response(502, ['Content-Type' => 'text/html'], '<html>Bad Gateway</html>'))
                ->sendSms('07700900321', 'tpl', ['body' => 'x'], 'dev-sms-1');
            $this->fail('expected a NotifyException');
        } catch (NotifyException $e) {
            $this->assertSame(502, $e->getCode());
            $this->assertStringContainsString('502', NotifyErrors::message($e));
        }
    }

    public function testLookupWithNoAnswerThrowsRatherThanSayingNotSent(): void
    {
        // SendSms only sends again when the lookup really found nothing
        $this->expectException(NotifyException::class);

        $this->clientReturning(new Response(404, ['Content-Type' => 'application/json'], '{"errors":[],"status_code":404}'))
            ->listNotifications(['reference' => 'dev-sms-1', 'template_type' => 'sms']);
    }

    public function testResolveGuzzleOptions(): void
    {
        $this->assertSame(['proxy' => 'http://p:3128'], NotifyClientFactory::resolveGuzzleOptions('http://p:3128'));
        $this->assertSame([], NotifyClientFactory::resolveGuzzleOptions('http://%shd_proxy%'));
        $this->assertSame([], NotifyClientFactory::resolveGuzzleOptions(''));
        $this->assertSame([], NotifyClientFactory::resolveGuzzleOptions(null));
    }

    public function testServiceFactoryBuildsFromConfig(): void
    {
        $sm = new ServiceManager();
        $sm->setService('config', ['email' => ['notify' => ['proxy' => 'http://p:3128']]]);

        $this->assertInstanceOf(
            NotifyClientFactory::class,
            (new NotifyClientFactoryFactory())($sm, NotifyClientFactory::class)
        );
    }
}

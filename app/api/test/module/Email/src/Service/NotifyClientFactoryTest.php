<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException;
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

    public function testNotifyErrorsKeepNotifysOwnMessage(): void
    {
        // Notify reads error responses itself, so Guzzle must not throw on them first. If it did,
        // the stored error would be Guzzle's text: the URL and a cut-off copy of the JSON.
        $body = json_encode([
            'errors' => [['error' => 'BadRequestError', 'message' => "Can't send to this recipient using a team-only API key"]],
            'status_code' => 400,
        ]);
        $handler = HandlerStack::create(new MockHandler([new Response(400, ['Content-Type' => 'application/json'], $body)]));
        $client = (new NotifyClientFactory(['handler' => $handler]))
            ->fromApiKey('test-11111111-1111-4111-8111-111111111111-22222222-2222-4222-8222-222222222222');

        try {
            $client->sendSms('07700900321', 'tpl', ['body' => 'x'], 'dev-sms-1');
            $this->fail('expected an ApiException');
        } catch (ApiException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame('BadRequestError: "Can\'t send to this recipient using a team-only API key"', $e->getMessage());
        }
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

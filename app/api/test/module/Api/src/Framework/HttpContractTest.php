<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Doctrine\ORM\OptimisticLockException;
use Dvsa\Contracts\Auth\Exceptions\InvalidTokenException;
use Dvsa\Contracts\Auth\OAuthClientInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Domain\Exception\RestResponseException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Exception\VersionConflictException;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Db\Service\Search\Search;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Create as CreatePresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Delete as DeletePresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Update as UpdatePresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\Si\ComplianceEpisode;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\GetList as PresidingTcList;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\HttpExchange;
use Dvsa\OlcsTest\Api\Framework\Support\HttpKernel;
use Dvsa\OlcsTest\Api\Framework\Support\JsonSnapshot;
use Dvsa\OlcsTest\Api\Framework\Support\LegacyJsonResult;
use Dvsa\OlcsTest\Api\Framework\Support\MvcHttpKernel;
use Firebase\JWT\ExpiredException;
use GuzzleHttp\Psr7\ServerRequest;
use Laminas\Http\Response\Stream;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ServerRequestInterface;

/**
 * What a client of the api gets back — status, headers, the exact body bytes — and what the api logs on
 * the way, for each kind of request the frontends (and the few external callers) send. Committed as JSON
 * under snapshots/http/.
 *
 * The api's business logic sits behind QueryHandlerManager and CommandHandlerManager, and the laminas-mvc
 * removal does not touch it, so here those managers are stand-ins that return or throw what each case
 * needs. Everything in front of them is real: routing, payload validation (PayloadValidationListener),
 * identity from the Authorization header, the controllers' exception-to-status mapping, JSON rendering,
 * the error pages, the request/response logging. That is exactly the part being replaced.
 *
 * The cases go through HttpKernel, so the same cases run against the replacement: add a kernel for the
 * new stack and run the provider against both. Until then, anything that changes these files is a change
 * a client can see.
 */
final class HttpContractTest extends FrameworkTestCase
{
    private const string SNAPSHOT_DIR = __DIR__ . '/snapshots/http';
    private const string UPDATE_COMMAND = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter HttpContractTest';
    private const string HOST = 'http://olcs-backend';

    /**
     * What Common\Service\Cqrs\RequestFactory puts on every frontend request
     */
    private const array FRONTEND_HEADERS = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'X-Correlation-Id' => 'framework-test-correlation-id',
    ];

    /**
     * Shaped only as the api checks before decoding (`Bearer x.y.z`); the decoding itself is mocked. Not
     * base64 JSON, so it reads as a placeholder to people and to secret scanning: this repository is
     * public, and a JWT-shaped test value raises a GitHub secret scanning alert.
     */
    private const string TOKEN = 'framework-test.not-a-real.token';

    #[DataProvider('cases')]
    public function testResponseMatchesSnapshot(ServerRequestInterface $request, ?\Closure $configure): void
    {
        $exchange = $this->kernel()->handle($request, $configure);

        JsonSnapshot::assertMatches(
            self::snapshotFile($this->dataName()),
            self::describe($request, $exchange),
            'The response to ' . $this->dataName(),
            self::UPDATE_COMMAND
        );
    }

    public function testEverySnapshotHasACase(): void
    {
        JsonSnapshot::assertNoOrphans(
            self::SNAPSHOT_DIR,
            array_map(self::snapshotFile(...), array_keys(iterator_to_array(self::cases()))),
            self::UPDATE_COMMAND
        );
    }

    /**
     * @return iterable<string, array{ServerRequestInterface, ?\Closure}>
     */
    public static function cases(): iterable
    {
        yield from self::queryCases();
        yield from self::commandCases();
        yield from self::validationCases();
        yield from self::routingCases();
        yield from self::identityCases();
        yield from self::otherControllerCases();
    }

    /**
     * GenericController::get() and getList(): results, and every exception it maps to a status
     *
     * @return iterable<string, array{ServerRequestInterface, ?\Closure}>
     */
    private static function queryCases(): iterable
    {
        $licence = self::get('/api/licence/101/?dto=' . urlencode(Licence::class));
        $list = self::get('/api/presiding-tc/?dto=' . urlencode(PresidingTcList::class) . '&sort=id&order=ASC');

        yield 'query single result' => [$licence, self::query(Licence::class, ['id' => 101, 'licNo' => 'OB1234567'])];

        // The exact bytes of the JSON encoding: slashes, HTML characters, quotes, non-ASCII, empty array vs object
        yield 'query result encoding' => [$licence, self::query(Licence::class, [
            'text' => "Café <b>&'\"/\\ ✓",
            'emptyList' => [],
            'emptyObject' => new \stdClass(),
            'float' => 1.0,
            'null' => null,
            'nested' => ['a' => [1, 2]],
        ])];

        yield 'query legacy JsonSerializable result' => [$licence, self::query(Licence::class, new LegacyJsonResult())];

        yield 'query list result' => [$list, self::query(PresidingTcList::class, [
            'result' => [['id' => 1], ['id' => 2]],
            'count' => 2,
            'count-unfiltered' => 5,
            'extraKey' => 'carried in extra',
        ])];

        yield 'query list result without counts' => [$list, self::query(PresidingTcList::class, ['result' => [['id' => 1]]])];

        // A list query that returns something without 'result' is answered as a single result
        yield 'query list answered as single result' => [$list, self::query(PresidingTcList::class, ['id' => 7])];

        yield 'query stream from a list query' => [$list, self::query(PresidingTcList::class, self::stream())];
        yield 'query stream from a single query' => [$licence, self::query(Licence::class, self::stream())];

        yield 'query not found' => [$licence, self::query(Licence::class, new NotFoundException())];
        yield 'query not ready' => [$licence, self::query(Licence::class, (new NotReadyException('Busy'))->setRetryAfter(30))];
        yield 'query forbidden' => [$licence, self::query(Licence::class, new ForbiddenException())];
        yield 'query domain exception' => [$licence, self::query(Licence::class, new ValidationException(['field' => ['ERR_X' => 'Is wrong']]))];
        yield 'query rest response exception' => [$licence, self::query(Licence::class, new RestResponseException('Teapot', 418))];
        yield 'query unexpected exception' => [$licence, self::query(Licence::class, new \RuntimeException('Something broke'))];

        // Not an \Exception, so GenericController does not catch it: laminas-mvc's dispatch-error handling does
        yield 'query unexpected error' => [$licence, self::query(Licence::class, new \TypeError('Something broke harder'))];

        yield 'query list not found' => [$list, self::query(PresidingTcList::class, new NotFoundException())];
        yield 'query list forbidden' => [$list, self::query(PresidingTcList::class, new ForbiddenException())];
        yield 'query list unexpected exception' => [$list, self::query(PresidingTcList::class, new \RuntimeException('List broke'))];
    }

    /**
     * GenericController::create(), update() and delete()
     *
     * @return iterable<string, array{ServerRequestInterface, ?\Closure}>
     */
    private static function commandCases(): iterable
    {
        $create = self::json('POST', '/api/presiding-tc/', ['name' => 'Framework Test', 'user' => 3]);
        $update = self::json('PUT', '/api/presiding-tc/7/', ['id' => 7, 'version' => 2, 'name' => 'Framework Test', 'user' => 3]);
        $delete = self::json('DELETE', '/api/presiding-tc/7/', ['id' => 7]);

        $result = (new Result())->addId('presidingTc', 7)->addMessage('Presiding TC saved');

        yield 'command create' => [$create, self::command(CreatePresidingTc::class, $result)];
        yield 'command update' => [$update, self::command(UpdatePresidingTc::class, $result)];
        yield 'command delete' => [$delete, self::command(DeletePresidingTc::class, $result)];

        yield 'command create forbidden' => [$create, self::command(CreatePresidingTc::class, new ForbiddenException())];
        yield 'command create domain exception' => [$create, self::command(CreatePresidingTc::class, new ValidationException(['name' => ['ERR' => 'Taken']]))];
        yield 'command create unexpected exception' => [$create, self::command(CreatePresidingTc::class, new \RuntimeException('Create broke'))];
        yield 'command update version conflict' => [$update, self::command(UpdatePresidingTc::class, new VersionConflictException())];
        yield 'command update optimistic lock' => [$update, self::command(UpdatePresidingTc::class, new OptimisticLockException('Locked', null))];
        yield 'command update not found' => [$update, self::command(UpdatePresidingTc::class, new NotFoundException())];
        yield 'command delete not found' => [$delete, self::command(DeletePresidingTc::class, new NotFoundException())];
    }

    /**
     * PayloadValidationListener: the DTO is built and validated before any controller runs
     *
     * @return iterable<string, array{ServerRequestInterface, ?\Closure}>
     */
    private static function validationCases(): iterable
    {
        yield 'validation fails on a query' => [self::get('/api/licence/abc/?dto=' . urlencode(Licence::class)), null];
        yield 'validation fails on a list query without sort' => [self::get('/api/presiding-tc/?dto=' . urlencode(PresidingTcList::class)), null];
        yield 'validation fails on a JSON command' => [self::json('POST', '/api/presiding-tc/', ['name' => '', 'user' => 'x']), null];

        $form = (new ServerRequest('POST', self::HOST . '/api/presiding-tc/', [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], 'name=Framework+Test&user=3'))->withParsedBody(['name' => 'Framework Test', 'user' => '3']);

        yield 'form-encoded command' => [$form, self::command(CreatePresidingTc::class, (new Result())->addId('presidingTc', 7))];

        $episode = static fn(string $xml): ServerRequestInterface => new ServerRequest(
            'POST',
            self::HOST . '/msi/compliance-episode',
            ['Content-Type' => 'text/xml'],
            $xml
        );

        // The one external caller: XmlController answers with a bare status, never a body
        yield 'xml command not xml' => [$episode('not xml'), null];
        yield 'xml command accepted' => [$episode('<?xml version="1.0"?><episode/>'), self::command(ComplianceEpisode::class, new Result())];
        yield 'xml command rejected' => [$episode('<?xml version="1.0"?><episode/>'), self::command(ComplianceEpisode::class, new ValidationException(['xml' => ['bad']]))];
    }

    /**
     * @return iterable<string, array{ServerRequestInterface, ?\Closure}>
     */
    private static function routingCases(): iterable
    {
        yield 'no such route' => [self::get('/api/no-such-thing/'), null];
        yield 'method with no route' => [self::json('PATCH', '/api/licence/101/', ['id' => 101]), null];
        yield 'not under /api' => [self::get('/'), null];
    }

    /**
     * Identity is resolved from the Authorization header while the application bootstraps
     *
     * @return iterable<string, array{ServerRequestInterface, ?\Closure}>
     */
    private static function identityCases(): iterable
    {
        $licence = '/api/licence/101/?dto=' . urlencode(Licence::class);

        yield 'bearer token identifies the user' => [
            self::get($licence, ['Authorization' => 'Bearer ' . self::TOKEN]),
            self::identity(['username' => 'usr611'], 'usr611'),
        ];

        // RefreshTokens relies on an expired token still reaching the api, as the anonymous user
        yield 'expired bearer token is anonymous' => [
            self::get($licence, ['Authorization' => 'Bearer ' . self::TOKEN]),
            self::identity(new InvalidTokenException('Expired', 0, new ExpiredException('Expired')), null),
        ];

        // Thrown while bootstrapping, so no response is produced: public/index.php's handlers take over
        yield 'invalid bearer token' => [
            self::get($licence, ['Authorization' => 'Bearer ' . self::TOKEN]),
            self::identity(new InvalidTokenException('Bad signature'), null, false),
        ];

        // Rejected before the token is decoded, also while bootstrapping
        yield 'malformed bearer token' => [self::get($licence, ['Authorization' => 'Bearer not-a-jwt']), null];
    }

    /**
     * The two controllers outside GenericController/XmlController
     *
     * @return iterable<string, array{ServerRequestInterface, ?\Closure}>
     */
    private static function otherControllerCases(): iterable
    {
        yield 'search' => [
            self::get('/search/licence?q=smith&page=1&limit=10'),
            static function (ServiceManager $serviceManager): void {
                $search = m::mock(Search::class);
                $search->expects('search')->with('smith', ['licence'], '1', '10')->andReturn(['Count' => 1, 'Results' => [['licNo' => 'OB1']]]);
                $serviceManager->setService('Elasticsearch\Search', $search);
            },
        ];

        yield 'scanning document without a scan id' => [
            (new ServerRequest('POST', self::HOST . '/scanning/document', ['Content-Type' => 'multipart/form-data; boundary=x'], ''))
                ->withParsedBody([]),
            null,
        ];
    }

    private function kernel(): HttpKernel
    {
        return new MvcHttpKernel();
    }

    /**
     * @param array<string, string> $headers
     */
    private static function get(string $path, array $headers = []): ServerRequestInterface
    {
        $request = new ServerRequest('GET', self::HOST . $path, array_merge(self::FRONTEND_HEADERS, $headers));
        parse_str((string) parse_url($path, PHP_URL_QUERY), $query);

        return $request->withQueryParams($query);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function json(string $method, string $path, array $data): ServerRequestInterface
    {
        return new ServerRequest($method, self::HOST . $path, self::FRONTEND_HEADERS, json_encode($data, JSON_THROW_ON_ERROR));
    }

    private static function stream(): Stream
    {
        $body = fopen('php://memory', 'w+b');
        assert($body !== false);
        fwrite($body, '%PDF-framework-test');
        rewind($body);

        $stream = new Stream();
        $stream->setStream($body);
        $stream->setStatusCode(200);
        $stream->getHeaders()->addHeaders([
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="framework-test.pdf"',
        ]);

        return $stream;
    }

    /**
     * The query handler answers with $outcome, or throws it
     */
    private static function query(string $dto, mixed $outcome): \Closure
    {
        return static function (ServiceManager $serviceManager) use ($dto, $outcome): void {
            $manager = m::mock(QueryHandlerManager::class);
            $expectation = $manager->expects('handleQuery')->with(m::type($dto));
            $outcome instanceof \Throwable ? $expectation->andThrow($outcome) : $expectation->andReturn($outcome);

            $serviceManager->setService('QueryHandlerManager', $manager);
        };
    }

    /**
     * The command handler answers with $outcome, or throws it
     */
    private static function command(string $dto, Result|\Throwable $outcome): \Closure
    {
        return static function (ServiceManager $serviceManager) use ($dto, $outcome): void {
            $manager = m::mock(CommandHandlerManager::class);
            $expectation = $manager->expects('handleCommand')->with(m::type($dto));
            $outcome instanceof \Throwable ? $expectation->andThrow($outcome) : $expectation->andReturn($outcome);

            $serviceManager->setService('CommandHandlerManager', $manager);
        };
    }

    /**
     * The token decodes to $claims (or throws them); the query then reports who it ran as
     *
     * @param array<string, string>|\Throwable $claims
     */
    private static function identity(array|\Throwable $claims, ?string $loginId, bool $reachesHandler = true): \Closure
    {
        return static function (ServiceManager $serviceManager) use ($claims, $loginId, $reachesHandler): void {
            $client = m::mock(OAuthClientInterface::class);
            $decode = $client->expects('decodeToken')->with(self::TOKEN);
            $claims instanceof \Throwable ? $decode->andThrow($claims) : $decode->andReturn($claims);
            $serviceManager->setService(OAuthClientInterface::class, $client);

            if ($loginId !== null) {
                $user = new User('pid', User::USER_TYPE_OPERATOR);
                $user->setLoginId($loginId);

                $users = m::mock(UserRepository::class);
                $users->expects('fetchEnabledIdentityByLoginId')->with($loginId)->andReturn($user);

                $repositories = $serviceManager->get('RepositoryServiceManager');
                assert($repositories instanceof RepositoryServiceManager);
                $repositories->setAllowOverride(true);
                $repositories->setService('User', $users);
            }

            if (!$reachesHandler) {
                return;
            }

            $manager = m::mock(QueryHandlerManager::class);
            $manager->expects('handleQuery')->with(m::type(Licence::class))->andReturnUsing(
                static fn(): array => [
                    'ranAs' => $serviceManager->get(AuthorizationService::class)->getIdentity()->getUser()->getLoginId(),
                ]
            );
            $serviceManager->setService('QueryHandlerManager', $manager);
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function describe(ServerRequestInterface $request, HttpExchange $exchange): array
    {
        $description = [
            'request' => [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri()->withScheme('')->withHost(''),
                'headers' => array_map(static fn(array $values): string => implode(', ', $values), $request->getHeaders()),
                'body' => (string) $request->getBody(),
            ],
        ];

        if ($exchange->response === null) {
            $uncaught = $exchange->uncaught;
            assert($uncaught !== null);
            $description['uncaught'] = $uncaught::class . ': ' . $uncaught->getMessage();
        } else {
            $response = $exchange->response;
            $body = (string) $response->getBody();
            $headers = $response->getHeaders();
            ksort($headers);

            $description['response'] = [
                'status' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'headers' => $headers,
                'body' => $body,
            ];

            if (json_validate($body)) {
                $description['response']['json'] = json_decode($body, true);
            }
        }

        $description['logs'] = array_map(self::describeLog(...), $exchange->logs);

        return $description;
    }

    /**
     * @return array<string, mixed>
     */
    private static function describeLog(LogRecord $record): array
    {
        $extra = $record->extra;
        // Wall-clock and random values
        unset($extra['microsecs']);

        if (($extra['sessionId'] ?? '') !== '') {
            $extra['sessionId'] = '(session id)';
        }

        ksort($extra);

        $context = $record->context;

        // Logger::logException(): where the exception was constructed (here, in this test) and the stack
        if (isset($context['trace'])) {
            $context['trace'] = '(stack trace)';
        }

        return [
            'level' => $record->level->getName(),
            'message' => preg_replace('~\n\S+\.php Line \d+$~', "\n{file} Line {line}", $record->message),
            'context' => self::normalise($context),
            'extra' => $extra,
        ];
    }

    /**
     * Keeps log context deterministic: objects become their class, exceptions their class and message
     */
    private static function normalise(mixed $value): mixed
    {
        return match (true) {
            $value instanceof \Throwable => ['exception' => $value::class, 'message' => $value->getMessage()],
            is_object($value) => '(object ' . $value::class . ')',
            is_array($value) => array_map(self::normalise(...), $value),
            default => $value,
        };
    }

    private static function snapshotFile(string $case): string
    {
        return self::SNAPSHOT_DIR . '/' . str_replace(' ', '-', $case) . '.json';
    }
}

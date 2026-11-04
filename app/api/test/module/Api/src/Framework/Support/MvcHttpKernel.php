<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use GuzzleHttp\Psr7\Response;
use Laminas\Http\Header\MultipleHeaderInterface;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Today's front door: a fresh api per request, run the way public/index.php runs it.
 *
 * The request is presented as php-fpm presents one — superglobals plus the raw body — and the api builds
 * its own Laminas request from them, so header parsing, base URL detection and body decoding are the
 * application's own. The body the client would receive is captured from the output buffer, as
 * SendResponseListener echoes it (or GenericController streams it straight to php://output). Status and
 * headers come from the response object. One thing cannot be observed in-process: headers a controller
 * sends itself with header() — the CLI SAPI discards them. Today only stream downloads do that.
 *
 * The superglobals are restored afterwards, whether or not the suite backs up globals.
 */
final class MvcHttpKernel implements HttpKernel
{
    private const array SUPERGLOBALS = ['_SERVER', '_GET', '_POST', '_FILES', '_COOKIE'];

    #[\Override]
    public function handle(ServerRequestInterface $request, ?\Closure $configure = null): HttpExchange
    {
        $saved = [];

        foreach (self::SUPERGLOBALS as $name) {
            $saved[$name] = $GLOBALS[$name];
        }

        try {
            $this->presentAsPhpFpmWould($request);

            return $this->run($request, $configure);
        } finally {
            foreach ($saved as $name => $value) {
                $GLOBALS[$name] = $value;
            }
        }
    }

    private function run(ServerRequestInterface $request, ?\Closure $configure): HttpExchange
    {

        try {
            $app = ApiApplication::bootstrap(Entrypoint::Web, $configure);
        } catch (\Throwable $e) {
            return new HttpExchange(null, [], $e);
        }

        $httpRequest = $app->serviceManager->get('Request');
        assert($httpRequest instanceof HttpRequest);
        // php://input cannot be faked; this is where the api reads it from
        $httpRequest->setContent((string) $request->getBody());

        $bufferLevel = ob_get_level();
        ob_start();

        try {
            $app->application->run();
        } catch (\Throwable $e) {
            return new HttpExchange(null, $app->logs->getRecords(), $e);
        } finally {
            $body = '';

            while (ob_get_level() > $bufferLevel) {
                $body = ob_get_clean() . $body;
            }
        }

        // The response that was sent: a listener or controller may have replaced the original
        $response = $app->application->getMvcEvent()->getResponse();
        assert($response instanceof HttpResponse);

        return new HttpExchange(
            new Response($response->getStatusCode(), self::headersAsSent($response), $body, '1.1', $response->getReasonPhrase()),
            $app->logs->getRecords()
        );
    }

    /**
     * The headers the client receives, which are not simply the response's header list: laminas-mvc's
     * AbstractResponseSender passes each one to header(), which replaces any earlier header of the same
     * name unless the header class allows several. A response holding Content-Type twice sends the last.
     *
     * @return array<string, list<string>>
     */
    private static function headersAsSent(HttpResponse $response): array
    {
        $sent = [];

        foreach ($response->getHeaders() as $header) {
            $name = $header->getFieldName();

            if (!$header instanceof MultipleHeaderInterface) {
                foreach (array_keys($sent) as $existing) {
                    if (strcasecmp($existing, $name) === 0) {
                        unset($sent[$existing]);
                    }
                }
            }

            $sent[$name][] = $header->getFieldValue();
        }

        return $sent;
    }

    private function presentAsPhpFpmWould(ServerRequestInterface $request): void
    {
        $uri = $request->getUri();
        $root = ApiApplication::root();
        $requestUri = $uri->getPath() . ($uri->getQuery() === '' ? '' : '?' . $uri->getQuery());

        $server = [
            'REQUEST_METHOD' => $request->getMethod(),
            'REQUEST_URI' => $requestUri,
            'QUERY_STRING' => $uri->getQuery(),
            'SERVER_PROTOCOL' => 'HTTP/' . $request->getProtocolVersion(),
            'HTTP_HOST' => $uri->getHost(),
            'SERVER_NAME' => $uri->getHost(),
            'SERVER_PORT' => '80',
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => $root . '/public/index.php',
            'PHP_SELF' => '/index.php',
            'DOCUMENT_ROOT' => $root . '/public',
            'REMOTE_ADDR' => '10.0.0.1',
            // Makes the RequestId log processor's identifier deterministic (it hashes this when present)
            'UNIQUE_ID' => 'framework-test',
            'REQUEST_TIME' => 1_700_000_000,
            'REQUEST_TIME_FLOAT' => 1_700_000_000.0,
        ];

        foreach ($request->getHeaders() as $name => $values) {
            $key = strtoupper(str_replace('-', '_', $name));
            $key = in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true) ? $key : 'HTTP_' . $key;
            $server[$key] = implode(', ', $values);
        }

        $_SERVER = $server;
        $_GET = $request->getQueryParams();
        $_POST = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        $_FILES = [];
        $_COOKIE = $request->getCookieParams();
    }
}

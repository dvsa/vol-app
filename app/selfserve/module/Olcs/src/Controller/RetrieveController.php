<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Service\Cqrs\Exception as CqrsException;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Command\RetrievalLink\RequestOtp as RequestOtpCommand;
use Dvsa\Olcs\Transfer\Command\RetrievalLink\VerifyOtp as VerifyOtpCommand;
use Dvsa\Olcs\Transfer\Query\RetrievalLink\Download as DownloadQuery;
use Dvsa\Olcs\Transfer\Query\RetrievalLink\Resolve as ResolveQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Header\SetCookie;
use Laminas\Http\Headers;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Retrieve a document (anonymous public journey).
 *
 * A recipient reaches this controller from an emailed link carrying an opaque :token. There is
 * NO login. Depending on the bundle's gate mode:
 *   - 'none' : the documents can be downloaded immediately.
 *   - 'otp'  : the recipient must request a one-time security code (emailed) and verify it before
 *              the download buttons are unlocked. A successful verification stores an opaque grant
 *              in a session-scoped, httponly, secure, SameSite=Lax cookie keyed to the token.
 *
 * Security notes:
 *   - The "not available" and OTP responses are deliberately non-committal: a bad/expired/unknown
 *     token, and an incorrect/expired code, must not reveal which of those it is (no oracle).
 *   - Only opaque values (token, memberRef) ever appear in markup or URLs - never internal ids.
 *   - The grant cookie name is derived from the token, so a grant issued for one token can never
 *     be presented for another.
 */
class RetrieveController extends AbstractController
{
    /** Prefix for the per-token grant cookie name. */
    private const GRANT_COOKIE_PREFIX = 'rtdlgrant_';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected RemoteAddress $remoteAddress,
        protected string $presignedFetchProxy = '',
        protected int $presignedFetchTimeout = 30,
        protected bool $grantCookieSecure = true
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Landing page: resolve the link and render the document summary (with downloads either
     * available, or gated behind OTP).
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        return $this->renderRetrievePage($this->getToken());
    }

    /**
     * Request a one-time code. Always renders the same neutral "if eligible, a code has been sent"
     * state so the response cannot be used to probe link/recipient existence.
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function requestOtpAction()
    {
        $token = $this->getToken();

        // Requesting a code is state-changing (it emails the recipient), so only accept the POST
        // form — a prefetcher, email link-scanner or crafted GET must not be able to trigger a send.
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('retrieve', ['token' => $token]);
        }

        try {
            $this->handleCommand(
                RequestOtpCommand::create(
                    [
                        'token' => $token,
                        'ip' => $this->getClientIp(),
                    ]
                )
            );
        } catch (CqrsException) {
            // Swallow deliberately: never reveal whether the send succeeded or the link exists.
        }

        return $this->renderRetrievePage($token, ['otpRequested' => true]);
    }

    /**
     * Verify a submitted one-time code. On success, store the grant cookie and redirect back to the
     * page with downloads unlocked. On failure, re-render with a non-committal error and the number
     * of attempts remaining.
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function verifyOtpAction()
    {
        $token = $this->getToken();

        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('retrieve', ['token' => $token]);
        }

        $form = $this->buildOtpForm($token);
        $form->setData((array) $this->params()->fromPost());

        if (!$form->isValid()) {
            return $this->renderRetrievePage($token, ['otpRequested' => true, 'form' => $form]);
        }

        $data = $form->getData();
        $code = $data['fields']['code'] ?? '';

        try {
            $response = $this->handleCommand(
                VerifyOtpCommand::create(
                    [
                        'token' => $token,
                        'code' => $code,
                        'ip' => $this->getClientIp(),
                    ]
                )
            );
        } catch (CqrsException) {
            // Token gone/expired while verifying - fall through to the neutral not-available page.
            return $this->notAvailable();
        }

        // Command results nest custom data under 'flags' (Result::toArray()), unlike query
        // handlers which return their array directly.
        $flags = $response->isOk() ? ($response->getResult()['flags'] ?? []) : [];
        $verified = !empty($flags['verified']);
        $grant = $flags['grant'] ?? null;
        $attemptsRemaining = $flags['attemptsRemaining'] ?? null;

        if ($verified && is_string($grant) && $grant !== '') {
            $this->setGrantCookie($token, $grant);

            return $this->redirect()->toRoute('retrieve', ['token' => $token]);
        }

        return $this->renderRetrievePage(
            $token,
            [
                'otpRequested' => true,
                'otpError' => true,
                'attemptsRemaining' => $attemptsRemaining,
                'form' => $form,
            ]
        );
    }

    /**
     * Stream a single document to the browser as an attachment.
     *
     * For otp-gated links the grant cookie is read and passed to the Download query; a missing grant
     * sends the recipient back to the OTP step. For 'none' links no grant is needed.
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function downloadAction()
    {
        $token = $this->getToken();
        $memberRef = (string) $this->params()->fromRoute('memberRef', '');

        // Resolve first: confirms the link is still live and tells us the gate mode.
        try {
            $resolveResponse = $this->handleQuery(ResolveQuery::create(['token' => $token]));
        } catch (CqrsException) {
            return $this->notAvailable();
        }

        if (!$resolveResponse->isOk()) {
            return $this->notAvailable();
        }

        $resolved = $resolveResponse->getResult();
        $gateMode = $resolved['gateMode'] ?? 'none';

        $downloadData = [
            'token' => $token,
            'memberRef' => $memberRef,
        ];

        if ($gateMode === 'otp') {
            $grant = $this->readGrantCookie($token);

            if ($grant === null) {
                // No valid session for this token - send them to the OTP step.
                return $this->redirect()->toRoute('retrieve', ['token' => $token]);
            }

            $downloadData['grant'] = $grant;
        }

        try {
            $downloadResponse = $this->handleQuery(DownloadQuery::create($downloadData));
        } catch (CqrsException) {
            return $this->downloadFailureResponse($token, $gateMode);
        }

        if (!$downloadResponse->isOk()) {
            return $this->downloadFailureResponse($token, $gateMode);
        }

        $filename = $this->displayFilenameFor($resolved, $memberRef);

        // On an S3 store the API returns a presigned URL (not a stream); fetch it server-side and
        // stream to the browser. WebDAV returns a stream we proxy directly.
        if (($resolved['downloadStrategy'] ?? 'stream') === 'presigned') {
            $result = $downloadResponse->getResult();
            $presignedUrl = is_array($result) ? ($result['presignedUrl'] ?? null) : null;

            if (!is_string($presignedUrl) || $presignedUrl === '') {
                return $this->downloadFailureResponse($token, $gateMode);
            }

            return $this->streamFromPresignedUrl($presignedUrl, $filename);
        }

        return $this->streamDocument($downloadResponse, $filename);
    }

    /**
     * Fetch a presigned URL server-side (it never reaches the browser) and stream the bytes to the
     * client as a forced attachment. Used for the S3 document store.
     *
     * The presigned URL points at S3, which the frontends reach through the shared forward proxy in
     * deployed environments (see presignedFetchOptions) — so this fetches with a proxy-aware HTTP
     * client, exactly like the API's Notify client, rather than a bare `fopen` that would ignore the
     * proxy. The URL is already authenticated by its signature, so no credentials are attached here.
     * The body is buffered and re-emitted via the MVC response (documents are single-digit MB); on
     * any transport or non-200 upstream error we render the neutral "not available" page.
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    private function streamFromPresignedUrl(string $url, ?string $displayFilename)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $upstream = $client->request('GET', $url, $this->presignedFetchOptions());
        } catch (\GuzzleHttp\Exception\GuzzleException) {
            return $this->notAvailable();
        }

        if ($upstream->getStatusCode() !== 200) {
            return $this->notAvailable();
        }

        // Buffer the body and emit it via the MVC response. Retrieve-via-Link documents are
        // single-digit MB, so buffering is cheap and reliable (a Laminas Response\Stream is not
        // emitted by the selfserve response sender). The presigned URL never reaches the client.
        $body = (string) $upstream->getBody();

        $filename = ($displayFilename !== null && $displayFilename !== '') ? $displayFilename : 'document';
        $filename = str_replace(['"', '\\', "\r", "\n", '/'], '', $filename);

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent($body);

        $headers = new Headers();
        $headers->addHeaderLine('Content-Type', 'application/octet-stream');
        $headers->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $headers->addHeaderLine('X-Content-Type-Options', 'nosniff');
        // Sensitive (e.g. police) documents must not linger in a shared-machine browser cache.
        $headers->addHeaderLine('Cache-Control', 'no-store');
        $headers->addHeaderLine('Pragma', 'no-cache');
        $headers->addHeaderLine('Content-Length', (string) strlen($body));
        $response->setHeaders($headers);

        return $response;
    }

    /**
     * Guzzle request options for the server-side presigned fetch.
     *
     * @return array<string, mixed>
     */
    private function presignedFetchOptions(): array
    {
        return [
            'timeout' => $this->presignedFetchTimeout,
            'connect_timeout' => 10,
            'http_errors' => false,
        ] + self::presignedFetchProxyOptions($this->presignedFetchProxy);
    }

    /**
     * Resolve the proxy option for the presigned fetch. Returns the shared egress proxy when one is
     * configured; an empty value or a still-unresolved `%placeholder%` (which happens locally, where
     * the cloud parameter providers are not active and the frontends are not behind the proxy) is
     * ignored so the client connects directly rather than pointing at a bogus proxy host. Mirrors
     * {@see \Dvsa\Olcs\Email\Transport\Factory\GovUkNotifyTransportFactoryFactory::resolveGuzzleOptions}.
     *
     * @return array{proxy?: string}
     */
    public static function presignedFetchProxyOptions(mixed $proxy): array
    {
        if (is_string($proxy) && $proxy !== '' && !str_contains($proxy, '%')) {
            return ['proxy' => $proxy];
        }

        return [];
    }

    /**
     * Resolve the link and build the appropriate view model. Any resolution failure (unknown,
     * expired, gone, error) renders the identical neutral "not available" page.
     *
     * @param array $flags otpRequested, otpError, attemptsRemaining, form
     *
     * @return ViewModel
     */
    private function renderRetrievePage(string $token, array $flags = []): ViewModel
    {
        try {
            $response = $this->handleQuery(ResolveQuery::create(['token' => $token]));
        } catch (CqrsException) {
            return $this->notAvailable();
        }

        if (!$response->isOk()) {
            return $this->notAvailable();
        }

        $result = $response->getResult();
        $gateMode = $result['gateMode'] ?? 'none';
        $documents = $result['documents'] ?? [];
        $expiresAt = $result['expiresAt'] ?? null;

        // For 'none' links downloads are always available. For 'otp' links the buttons are unlocked
        // only when a grant cookie is present for this token (the API re-checks it on download).
        $unlocked = ($gateMode !== 'otp') || ($this->readGrantCookie($token) !== null);

        $form = null;
        if ($gateMode === 'otp' && !$unlocked) {
            $form = $flags['form'] ?? $this->buildOtpForm($token);
        }

        $view = new ViewModel(
            [
                'token' => $token,
                'gateMode' => $gateMode,
                'documents' => $documents,
                'expiresAt' => $expiresAt,
                'unlocked' => $unlocked,
                'form' => $form,
                'otpRequested' => (bool) ($flags['otpRequested'] ?? false),
                'otpError' => (bool) ($flags['otpError'] ?? false),
                'attemptsRemaining' => $flags['attemptsRemaining'] ?? null,
                'requestOtpUrl' => $this->url()->fromRoute('retrieve/request-otp', ['token' => $token]),
            ]
        );
        $view->setTemplate('olcs/retrieve/index');

        return $view;
    }

    /**
     * Neutral "this link is no longer available" page. Non-committal by design.
     *
     * @return ViewModel
     */
    private function notAvailable(): ViewModel
    {
        $view = new ViewModel();
        $view->setTemplate('olcs/retrieve/not-available');

        return $view;
    }

    /**
     * Where to send the recipient when a download cannot be served: otp links go back to the OTP
     * step (in case the grant expired), everything else gets the neutral page.
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    private function downloadFailureResponse(string $token, string $gateMode)
    {
        if ($gateMode === 'otp') {
            // The stored grant was rejected (e.g. expired) - clear it so the page falls back to the
            // OTP step rather than showing enabled-but-broken download links.
            $this->clearGrantCookie($token);

            return $this->redirect()->toRoute('retrieve', ['token' => $token]);
        }

        return $this->notAvailable();
    }

    /**
     * Proxy the API's file response to the browser, forwarding only safe headers and forcing an
     * attachment disposition (synthesised from the display filename if the API omitted one).
     *
     * @return \Laminas\Http\Response
     */
    private function streamDocument(\Common\Service\Cqrs\Response $response, ?string $displayFilename)
    {
        $httpResponse = $response->getHttpResponse();

        // Forward only content-framing headers. We deliberately DROP the API's Content-Disposition:
        // the shared API downloader emits `inline` (+ text/html) for html files, which on this
        // unauthenticated public origin would let a bundled .html document execute script (stored
        // XSS). Force `attachment` ourselves — always — and stop content-type sniffing.
        $allowedHeaders = ['Content-Encoding', 'Content-Type', 'Content-Length'];

        $headers = new Headers();
        foreach ($httpResponse->getHeaders() as $header) {
            if (in_array($header->getFieldName(), $allowedHeaders, true)) {
                $headers->addHeader($header);
            }
        }

        $filename = ($displayFilename !== null && $displayFilename !== '') ? $displayFilename : 'document';
        // Strip anything that could break the header / traverse the path.
        $filename = str_replace(['"', '\\', "\r", "\n", '/'], '', $filename);
        $headers->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $headers->addHeaderLine('X-Content-Type-Options', 'nosniff');
        // Sensitive (e.g. police) documents must not linger in a shared-machine browser cache.
        $headers->addHeaderLine('Cache-Control', 'no-store');
        $headers->addHeaderLine('Pragma', 'no-cache');

        $httpResponse->setHeaders($headers);

        return $httpResponse;
    }

    /**
     * Look up the display filename for a memberRef within a resolved bundle.
     */
    private function displayFilenameFor(array $resolved, string $memberRef): ?string
    {
        foreach ($resolved['documents'] ?? [] as $document) {
            if (($document['memberRef'] ?? null) === $memberRef) {
                return $document['displayFilename'] ?? null;
            }
        }

        return null;
    }

    /**
     * Build the OTP entry form, pointed at the verify-otp route for this token.
     *
     * @return \Common\Form\Form
     */
    private function buildOtpForm(string $token)
    {
        /** @var \Common\Form\Form $form */
        $form = $this->formHelper->createForm('RetrieveOtp');
        $form->setAttribute('action', $this->url()->fromRoute('retrieve/verify-otp', ['token' => $token]));

        return $form;
    }

    /**
     * The opaque token from the route.
     */
    private function getToken(): string
    {
        return (string) $this->params()->fromRoute('token', '');
    }

    /**
     * Best-effort client IP for rate-limiting / audit on the API side.
     */
    private function getClientIp(): string
    {
        return (string) $this->remoteAddress->getIpAddress();
    }

    /**
     * Per-token grant cookie name. Deriving the name from the token means a grant issued for one
     * token can never be read on the request for a different token.
     */
    private function grantCookieName(string $token): string
    {
        return self::GRANT_COOKIE_PREFIX . substr(hash('sha256', $token), 0, 32);
    }

    /**
     * Restrict the cookie to this token's URL space as defence in depth.
     */
    private function grantCookiePath(string $token): string
    {
        return '/retrieve/' . rawurlencode($token);
    }

    /**
     * Store the grant in a session-scoped, httponly, secure, SameSite=Lax cookie keyed to the token.
     */
    private function setGrantCookie(string $token, string $grant): void
    {
        $cookie = new SetCookie(
            $this->grantCookieName($token),
            $grant,
            null,                       // expires: null => session cookie (cleared when the browser closes)
            $this->grantCookiePath($token),
            null,                       // domain: default (current host)
            $this->grantCookieSecure,   // secure (config-driven: true in real envs, false in local HTTP dev)
            true,                       // httponly
            null,                       // maxAge
            null,                       // version
            SetCookie::SAME_SITE_LAX
        );

        $this->getResponse()->getHeaders()->addHeader($cookie);
    }

    /**
     * Expire the grant cookie for this token (used when a stored grant is no longer accepted).
     */
    private function clearGrantCookie(string $token): void
    {
        $cookie = new SetCookie(
            $this->grantCookieName($token),
            '',
            (int) strtotime('-1 year'),
            $this->grantCookiePath($token),
            null,
            $this->grantCookieSecure,
            true,
            null,
            null,
            SetCookie::SAME_SITE_LAX
        );

        $this->getResponse()->getHeaders()->addHeader($cookie);
    }

    /**
     * Read the grant cookie for this token, or null if absent/empty.
     */
    private function readGrantCookie(string $token): ?string
    {
        $cookies = $this->getRequest()->getCookie();

        if (!$cookies instanceof Cookie) {
            return null;
        }

        $name = $this->grantCookieName($token);

        if (!$cookies->offsetExists($name)) {
            return null;
        }

        $value = (string) $cookies->offsetGet($name);

        return $value === '' ? null : $value;
    }
}

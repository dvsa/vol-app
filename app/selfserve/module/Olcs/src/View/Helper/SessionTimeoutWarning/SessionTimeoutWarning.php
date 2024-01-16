<?php
declare(strict_types=1);

namespace Olcs\View\Helper\SessionTimeoutWarning;

use Laminas\View\Helper\HeadMeta;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\HelperInterface;

/**
 * @See SessionTimeoutWarningFactory
 */
class SessionTimeoutWarning extends AbstractHelper implements HelperInterface
{
    const META_TAG_NAME_SESSION_WARNING_TIMEOUT = 'session-warning-timeout';
    const META_TAG_NAME_SESSION_REDIRECT_TIMEOUT = 'session-redirect-timeout';
    const META_TAG_NAME_TIMEOUT_REDIRECT_URL = 'timeout-redirect-url';
    /**
     * Determines if the session timeout warning modal helper is enabled.
     * Required.
     * @var bool
     */
    protected $enabled;

    /**
     * The timeout in milliseconds before the modal appears warning the user of impending timeout.
     * Required if enabled.
     * @var integer
     */
    protected $secondsBeforeExpiryWarning;

    /**
     * The URL to redirect to when the $intervalTimeout is reached.
     * Required if enabled.
     * @var string
     */
    protected $timeoutRedirectUrl;

    private HeadMeta $headMeta;

    /**
     * SessionTimeoutWarning constructor.
     * @param bool $enabled
     * @param int $secondsBeforeExpiryWarning
     * @param string $timeoutRedirectUrl
     */
    public function __construct(
        HeadMeta $headMeta,
        bool $enabled,
        int $secondsBeforeExpiryWarning,
        string $timeoutRedirectUrl
    )
    {
        $this->headMeta = $headMeta;
        $this->enabled = $enabled;
        $this->secondsBeforeExpiryWarning = $secondsBeforeExpiryWarning;
        $this->timeoutRedirectUrl = $timeoutRedirectUrl;
    }

    /**
     * Generates a string of head <meta> tags with configuration. If enabled.
     *
     * @param int|null $indent
     * @return string
     */
    public function generateHeadMetaTags(int $indent = null): string
    {
        if (!$this->enabled) {
            return '';
        }

        $this->headMeta->setView($this->getView());
        $this->headMeta->appendName(self::META_TAG_NAME_SESSION_WARNING_TIMEOUT, $this->getWarningTimeout());
        $this->headMeta->appendName(self::META_TAG_NAME_SESSION_REDIRECT_TIMEOUT, $this->getRedirectTimeout());
        $this->headMeta->appendName(self::META_TAG_NAME_TIMEOUT_REDIRECT_URL, $this->timeoutRedirectUrl);

        return $this->headMeta->toString($indent);
    }

    /**
     * Get the seconds before the expiry to show the warning modal
     *
     * @return int
     */
    public function getSecondsBeforeExpiryWarning(): int
    {
        return $this->secondsBeforeExpiryWarning;
    }

    /**
     * Returns the timeout in seconds before the page should redirect to a session timeout page.
     * @return int
     */
    private function getRedirectTimeout(): int
    {
        return $this->getSessionMaxIdleLifetime();
    }

    /**
     * Returns the timeout in seconds before the session warning timeout modal is displayed.
     * @return int
     */
    private function getWarningTimeout(): int
    {
        return $this->getSessionMaxIdleLifetime() - $this->getSecondsBeforeExpiryWarning();
    }


    /**
     * Fetches the time in seconds a session can be idle for.
     * @return int
     */
    private function getSessionMaxIdleLifetime(): int
    {
        $maxLifeTime = $this->getSessionGcMaxLifeTime();

        if (empty($maxLifeTime)) {
            $maxLifeTime = 1440;
        }

        return (int) $maxLifeTime;
    }

    /**
     * Returns the PHP.ini setting 'session.gc_maxlifetime'.
     *
     * @return string
     */
    private function getSessionGcMaxLifeTime(): string
    {
        return ini_get('session.gc_maxlifetime');
    }
}

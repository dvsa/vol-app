<?php

namespace Olcs\Logging\Log\Processor;

use Laminas\Session\Container;
use Laminas\Session\ManagerInterface as Manager;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Adds a fingerprint of the PHP session, so that one session's log records can be grouped.
 *
 * Never the session id itself: in selfserve and internal it is the session cookie, and the session holds the
 * signed-in user's identity, so anyone reading the logs could take the session over. HideCredentials does not
 * catch it either, as it runs before this processor (Monolog runs the processor pushed last first, and it is
 * pushed after the configured ones).
 *
 * And only for a session that has already started. Starting one here made every logged request open a
 * session; the api, which has no use for one, wrote a session file and sent a session cookie each time.
 *
 * Standard formats the fingerprint as openam_session_token, a name kept so existing log queries still work.
 */
class SessionId implements ProcessorInterface
{
    /**
     * Hex characters of the SHA-256 digest kept: 64 bits, enough to tell sessions apart in the logs
     */
    private const int FINGERPRINT_LENGTH = 16;

    protected ?Manager $sessionManager = null;

    public function setSessionManager(Manager $sessionManager): SessionId
    {
        $this->sessionManager = $sessionManager;
        return $this;
    }

    public function getSessionManager(): Manager
    {
        if (!$this->sessionManager instanceof Manager) {
            $this->sessionManager = Container::getDefaultManager();
        }
        return $this->sessionManager;
    }

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $manager = $this->getSessionManager();

        if (!$manager->sessionExists()) {
            return $record;
        }

        $id = $manager->getId();

        if ($id === '') {
            return $record;
        }

        $extra = $record->extra;
        $extra['sessionId'] = self::fingerprint($id);

        return $record->with(extra: $extra);
    }

    public static function fingerprint(string $sessionId): string
    {
        return substr(hash('sha256', $sessionId), 0, self::FINGERPRINT_LENGTH);
    }
}

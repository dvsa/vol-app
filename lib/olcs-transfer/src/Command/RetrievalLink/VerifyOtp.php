<?php

namespace Dvsa\Olcs\Transfer\Command\RetrievalLink;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;

/**
 * Carries the plaintext one-time code, so its content is kept out of the request logs.
 *
 * @Transfer\RouteName("backend/retrieval-link/verify-otp")
 * @Transfer\Method("POST")
 */
class VerifyOtp extends AbstractCommand implements LoggerOmitContentInterface
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $token;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $code;

    /**
     * @var string|null
     * @Transfer\Optional
     */
    protected $ip;

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get ip
     *
     * @return string|null
     */
    public function getIp()
    {
        return $this->ip;
    }
}

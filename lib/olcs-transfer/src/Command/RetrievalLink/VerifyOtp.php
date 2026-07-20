<?php

namespace Dvsa\Olcs\Transfer\Command\RetrievalLink;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/retrieval-link/verify-otp")
 * @Transfer\Method("POST")
 */
class VerifyOtp extends AbstractCommand
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

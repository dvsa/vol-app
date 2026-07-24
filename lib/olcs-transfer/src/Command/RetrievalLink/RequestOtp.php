<?php

namespace Dvsa\Olcs\Transfer\Command\RetrievalLink;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/retrieval-link/request-otp")
 * @Transfer\Method("POST")
 */
class RequestOtp extends AbstractCommand
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $token;

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
     * Get ip
     *
     * @return string|null
     */
    public function getIp()
    {
        return $this->ip;
    }
}

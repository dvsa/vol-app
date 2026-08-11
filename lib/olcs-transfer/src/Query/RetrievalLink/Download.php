<?php

namespace Dvsa\Olcs\Transfer\Query\RetrievalLink;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/retrieval-link/download")
 */
class Download extends AbstractQuery
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
    protected $memberRef;

    /**
     * Post-OTP session grant. Optional: only required for otp-gated links, where the API
     * re-checks it as defence-in-depth against a download that bypasses the OTP step.
     *
     * @var string|null
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $grant;

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
     * Get member ref
     *
     * @return string
     */
    public function getMemberRef()
    {
        return $this->memberRef;
    }

    /**
     * Get session grant
     *
     * @return string|null
     */
    public function getGrant()
    {
        return $this->grant;
    }
}

<?php

/**
 * Download Cpms Report
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\Cpms;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/cpms/report/named-single")
 * @Transfer\Method("PUT")
 */
final class DownloadReport extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $reference;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $token;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     * @Transfer\Optional
     */
    protected $filename;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $user;

    /**
     * Gets the value of reference.
     *
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Gets the value of token.
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Gets the value of filename.
     *
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}

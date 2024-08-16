<?php

/**
 * Send Continuation Not Sought Email
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Send Continuation Not Sought Email
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class SendContinuationNotSought extends AbstractIdOnlyCommand
{
    /**
     * @var array
     */
    protected $licences = [];

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * Gets the value of licences.
     *
     * @return array
     */
    public function getLicences()
    {
        return $this->licences;
    }

    /**
     * Gets the value of date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}

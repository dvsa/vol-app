<?php

/**
 * Send email to notify that EBSR map has been requested
 */

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Send email to notify that EBSR map has been requested
 */
final class SendEbsrRequestMap extends AbstractIdOnlyCommand
{
    protected $pdfType;

    /**
     * The pdf type we're sending the email for
     *
     * @return string
     */
    public function getPdfType()
    {
        return $this->pdfType;
    }
}

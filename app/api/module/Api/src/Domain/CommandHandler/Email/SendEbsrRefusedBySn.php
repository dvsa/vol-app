<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

/**
 * Send Ebsr Refused By SN Email
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class SendEbsrRefusedBySn extends SendEbsrAbstract
{
    protected $template = 'ebsr-refused-by-sn';
}

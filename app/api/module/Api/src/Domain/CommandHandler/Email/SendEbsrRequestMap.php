<?php

/**
 * Send Ebsr Request Map
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

/**
 * Send Ebsr Request Map
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class SendEbsrRequestMap extends SendEbsrAbstract
{
    protected $template = 'ebsr-request-map';
}

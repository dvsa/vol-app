<?php

/**
 * Send Ebsr Errors Email
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Doctrine\ORM\Query;

/**
 * Send Ebsr Errors Email
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class SendEbsrErrors extends SendEbsrAbstract
{
    protected $template = ['ebsr-data-error-start', 'ebsr-data-error-list', 'ebsr-data-error-end'];
}

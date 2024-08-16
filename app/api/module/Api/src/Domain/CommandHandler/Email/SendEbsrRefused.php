<?php

/**
 * Send Ebsr Refused Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Doctrine\ORM\Query;

/**
 * Send Ebsr Refused Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */
final class SendEbsrRefused extends SendEbsrAbstract
{
    protected $template = 'ebsr-refused';
}

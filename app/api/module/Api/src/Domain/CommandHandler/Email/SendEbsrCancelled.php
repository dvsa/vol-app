<?php

/**
 * Send Ebsr Cancelled Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Doctrine\ORM\Query;

/**
 * Send Ebsr Cancelled Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */
final class SendEbsrCancelled extends SendEbsrAbstract
{
    protected $template = 'ebsr-cancelled';
}

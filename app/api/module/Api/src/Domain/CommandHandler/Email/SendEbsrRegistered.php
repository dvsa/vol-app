<?php

/**
 * Send Ebsr Received Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Doctrine\ORM\Query;

/**
 * Send Ebsr Received Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */
final class SendEbsrRegistered extends SendEbsrAbstract
{
    protected $template = 'ebsr-registered';
}

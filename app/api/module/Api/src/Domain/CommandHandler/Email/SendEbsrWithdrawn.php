<?php

/**
 * Send Ebsr Withdrawn Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Doctrine\ORM\Query;

/**
 * Send Ebsr Withdrawn Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */
final class SendEbsrWithdrawn extends SendEbsrAbstract
{
    protected $template = 'ebsr-withdrawn';
}

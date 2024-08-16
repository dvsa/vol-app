<?php

/**
 * Send Ebsr Refreshed Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Doctrine\ORM\Query;

/**
 * Send Ebsr Refreshed Email
 *
 * @author Craig R <uk@valtech.co.uk>
 */
final class SendEbsrRefreshed extends SendEbsrAbstract
{
    protected $template = 'ebsr-refreshed';
}

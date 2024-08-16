<?php

/**
 * Send Ebsr Cancelled Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrCancelled;

/**
 * Send Ebsr Cancelled Email Test
 * @group ebsrEmails
 *
 * @author Craig R <uk@valtech.co.uk>
 */
class SendEbsrCancelledTest extends SendEbsrRegCancelEmailTestAbstract
{
    protected $template = 'ebsr-cancelled';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrCancelled::class;
    protected $cmdClass = SendEbsrCancelled::class;
}

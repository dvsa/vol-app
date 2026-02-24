<?php

declare(strict_types=1);

/**
 * Send Ebsr Received Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrReceived;

/**
 * Send Ebsr Received Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\Group('ebsrEmails')]
class SendEbsrReceivedTest extends SendEbsrEmailTestAbstract
{
    protected $template = 'ebsr-received';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrReceived::class;
    protected const CMD_CLASS = SendEbsrReceived::class;
}

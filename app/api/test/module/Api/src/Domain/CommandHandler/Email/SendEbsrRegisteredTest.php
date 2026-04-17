<?php

declare(strict_types=1);

/**
 * Send Ebsr Registered Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRegistered;

/**
 * Send Ebsr Registered Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\Group('ebsrEmails')]
class SendEbsrRegisteredTest extends SendEbsrRegCancelEmailTestAbstract
{
    protected $template = 'ebsr-registered';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrRegistered::class;
    protected const CMD_CLASS = SendEbsrRegistered::class;
}

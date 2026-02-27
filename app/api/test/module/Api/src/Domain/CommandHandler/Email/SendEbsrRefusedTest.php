<?php

declare(strict_types=1);

/**
 * Send Ebsr Refused Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefused;

/**
 * Send Ebsr Refused Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\Group('ebsrEmails')]
class SendEbsrRefusedTest extends SendEbsrEmailTestAbstract
{
    protected $template = 'ebsr-refused';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrRefused::class;
    protected const CMD_CLASS = SendEbsrRefused::class;
}

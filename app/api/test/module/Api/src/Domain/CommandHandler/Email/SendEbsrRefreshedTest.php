<?php

declare(strict_types=1);

/**
 * Send Ebsr Refreshed Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefreshed;

/**
 * Send Ebsr Refreshed Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\Group('ebsrEmails')]
class SendEbsrRefreshedTest extends SendEbsrEmailTestAbstract
{
    protected $template = 'ebsr-refreshed';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrRefreshed::class;
    protected const CMD_CLASS = SendEbsrRefreshed::class;
}

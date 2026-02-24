<?php

declare(strict_types=1);

/**
 * Send Ebsr Withdrawn Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrWithdrawn;

/**
 * Send Ebsr Withdrawn Email Test
 *
 * @author Craig R <uk@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\Group('ebsrEmails')]
class SendEbsrWithdrawnTest extends SendEbsrEmailTestAbstract
{
    protected $template = 'ebsr-withdrawn';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrWithdrawn::class;
    protected const CMD_CLASS = SendEbsrWithdrawn::class;
}

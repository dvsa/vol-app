<?php

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
 * @group ebsrEmails
 *
 * @author Craig R <uk@valtech.co.uk>
 */
class SendEbsrWithdrawnTest extends SendEbsrEmailTestAbstract
{
    protected $template = 'ebsr-withdrawn';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrWithdrawn::class;
    protected $cmdClass = SendEbsrWithdrawn::class;
}

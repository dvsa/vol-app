<?php

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
 * @group ebsrEmails
 *
 * @author Craig R <uk@valtech.co.uk>
 */
class SendEbsrRegisteredTest extends SendEbsrRegCancelEmailTestAbstract
{
    protected $template = 'ebsr-registered';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrRegistered::class;
    protected $cmdClass = SendEbsrRegistered::class;
}

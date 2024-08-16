<?php

/**
 * Send Ebsr Request Map Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRequestMap;

/**
 * Send Ebsr Request Map Test
 * @group ebsrEmails
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendEbsrRequestMapTest extends SendEbsrEmailTestAbstract
{
    protected $template = 'ebsr-request-map';
    protected $sutClass = \Dvsa\Olcs\Api\Domain\CommandHandler\Email\SendEbsrRequestMap::class;
    protected $cmdClass = SendEbsrRequestMap::class;

    protected $pdfType = 'pdf type';

    protected $cmdData = [
        'id' => 1234,
        'pdfType' => 'pdf type'
    ];
}

<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class SendBSRNotificationToLTAs extends AbstractCommand
{
    use Identity;

    /**
     * @var array
     */
    protected $docs = [];

    public function getDocs(): array
    {
        return $this->docs;
    }
}

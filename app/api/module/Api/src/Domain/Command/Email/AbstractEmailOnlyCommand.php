<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\Email;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

abstract class AbstractEmailOnlyCommand extends AbstractCommand
{
    /** @var string */
    protected $emailAddress = '';

    /** @var string */
    protected $translateToWelsh = 'N';

    /**
     * @var array
     */
    protected $docs = [];

    public function getDocs(): array
    {
        return $this->docs;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function shouldTranslateToWelsh(): string
    {
        //Strip out potential int(0) which is the default value on Licence
        return ($this->translateToWelsh === 'Y') ? 'Y' : 'N';
    }
}

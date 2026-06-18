<?php

namespace Dvsa\Olcs\Transfer\Command;

/**
 * WithdrawApplicationInterface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface WithdrawApplicationInterface extends CommandInterface
{
    public function getId();
    public function getReason();
}

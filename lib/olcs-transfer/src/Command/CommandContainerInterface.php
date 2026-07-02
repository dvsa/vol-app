<?php

/**
 * Command Container Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command;

use Laminas\InputFilter\InputFilterInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Command Container Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface CommandContainerInterface
{
    public function setInputFilter(InputFilterInterface $inputFilter);

    public function getInputFilter();

    public function setDto(CommandInterface $dto);

    public function getDto();

    public function setRouteName($routeName);

    public function getRouteName();

    public function setMethod($method);

    public function getMethod();

    public function isValid();

    public function getMessages();
}

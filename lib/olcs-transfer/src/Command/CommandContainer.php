<?php

/**
 * Command Container
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command;

use Laminas\InputFilter\InputFilterInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Command Container
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommandContainer implements CommandContainerInterface
{
    protected $routeName;

    protected $method;

    protected $hasValidated = false;

    /**
     * @var InputFilterInterface
     */
    protected $inputFilter;

    /**
     * @var CommandInterface
     */
    protected $dto;

    #[\Override]
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    #[\Override]
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    #[\Override]
    public function setDto(CommandInterface $dto)
    {
        $this->dto = $dto;
    }

    #[\Override]
    public function getDto()
    {
        return $this->dto;
    }

    #[\Override]
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    #[\Override]
    public function getRouteName()
    {
        return $this->routeName;
    }

    #[\Override]
    public function setMethod($method)
    {
        $this->method = $method;
    }

    #[\Override]
    public function getMethod()
    {
        return $this->method;
    }

    #[\Override]
    public function isValid()
    {
        $this->hasValidated = true;

        $this->inputFilter->setData($this->dto->getArrayCopy());

        $this->dto->exchangeArray($this->inputFilter->getValues());

        return $this->inputFilter->isValid();
    }

    #[\Override]
    public function getMessages()
    {
        if ($this->hasValidated === false) {
            throw new \Exception('Validation has not yet occurred');
        }

        return $this->inputFilter->getMessages();
    }
}

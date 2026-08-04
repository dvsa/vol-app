<?php

declare(strict_types=1);

namespace Common\Form;

use Laminas\Form\Element\Csrf;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\NotEmpty;
use Common\InputFilter\ChainValidatedInput;

/**
 * A trait to satisfy the FormWithCsrfInterface interface.
 *
 * @see FormWithCsrfInterface
 * @see \CommonTest\Form\View\Helper\FormWithCsrfTraitTest
 */
trait FormWithCsrfTrait
{
    /**
     * Initialises a child csrf element.
     *
     * Should ideally be called by the constructor of any form that uses this trait.
     */
    protected function initialiseCsrf(): void
    {
        // Build element
        $csrfElement = new Csrf(FormWithCsrfInterface::SECURITY);
        $this->add($csrfElement);

        // Build input
        $input = new ChainValidatedInput(FormWithCsrfInterface::SECURITY);
        $validatorChain = $input->getValidatorChain();
        $validatorChain->attach($csrfElement->getCsrfValidator());

        $this->getInputFilter()->add($input);
    }

    public function getCsrfElement(): Csrf
    {
        return $this->get(FormWithCsrfInterface::SECURITY);
    }

    public function getCsrfInput(): \Laminas\InputFilter\InputInterface
    {
        return $this->getInputFilter()->get(FormWithCsrfInterface::SECURITY);
    }
}

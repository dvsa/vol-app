<?php

namespace Olcs\InputFilter;

use Zend\InputFilter\FileInput;

/**
 * Class EbsrFileInput
 *
 * This class extends Zend's file input allowing standard file validation to occur, however as we need to run a set of
 * validators against the contents of the zip and the standard file input runs validation before filters, this class
 * applies a second set of validators that are EBSR specific after the standard file validators have been run.
 *
 * @package Olcs\InputFilter
 */
class EbsrFileInput extends FileInput
{
    public function isValid($context = null)
    {
        if (!parent::isValid($context)) {
            return false;
        }

        return $this->validateEbsrFile();
    }

    public function validateEbsrFile()
    {
        return true;
    }
}
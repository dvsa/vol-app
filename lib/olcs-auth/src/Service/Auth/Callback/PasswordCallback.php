<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Password Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PasswordCallback extends AbstractTextPromptCallback
{
    /**
     * @var string
     */
    protected $type = 'PasswordCallback';
}

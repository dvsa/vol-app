<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Name Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NameCallback extends AbstractTextPromptCallback
{
    /**
     * @var string
     */
    protected $type = 'NameCallback';
}

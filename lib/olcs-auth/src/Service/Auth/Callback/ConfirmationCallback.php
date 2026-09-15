<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Confirmation Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConfirmationCallback implements CallbackInterface
{
    /**
     * Create a confirmation callback
     *
     * @param string $name Name
     *
     * @return void
     */
    public function __construct(private $name)
    {
    }

    /**
     * To array
     *
     * @return array
     */
    #[\Override]
    public function toArray()
    {
        return [
            'type' => 'ConfirmationCallback',
            'output' => [
                ['name' => 'prompt', 'value' => ''],
                ['name' => 'messageType', 'value' => 0],
                ['name' => 'options', 'value' => ['Submit', 'Cancel']],
                ['name' => 'optionType', 'value' => -1],
                ['name' => 'defaultOption', 'value' => 0]
            ],
            'input' => [['name' => $this->name, 'value' => 0]]
        ];
    }
}

<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Callback;

/**
 * Abstract Text Prompt Callback
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTextPromptCallback implements CallbackInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * Construct the object
     *
     * @param string $label Label
     * @param string $name  Name
     * @param string $value Value
     *
     * @return void
     */
    public function __construct(private $label, private $name, protected $value)
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
            'type' => $this->type,
            'output' => [['name' => 'prompt', 'value' => $this->label]],
            'input' => [
                [
                    'name' => $this->name,
                    'value' => $this->getFilteredValue()
                ]
            ]
        ];
    }

    /**
     * Get filtered value
     *
     * @return string
     */
    protected function getFilteredValue()
    {
        return $this->value;
    }
}

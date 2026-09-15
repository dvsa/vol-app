<?php

namespace Common\Service\Qa\Custom\Bilateral;

class RadioFactory
{
    /**
     * Create a Radio element instance with the supplied name
     *
     * @param string $name
     *
     * @return Radio
     */
    public function create($name)
    {
        return new Radio($name);
    }
}

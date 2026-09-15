<?php

namespace Common\Service\Qa;

use RuntimeException;

class FieldsetPopulatorProvider
{
    /** @var array */
    private $fieldsetPopulators = [];

    /**
     * Get an implementation of FieldsetPopulatorInterface corresponding to the supplied form control type
     *
     * @param string $type
     *
     * @throws RuntimeException
     */
    public function get($type)
    {
        if (!isset($this->fieldsetPopulators[$type])) {
            throw new RuntimeException('Fieldset populator not found: ' . $type);
        }

        return $this->fieldsetPopulators[$type];
    }

    /**
     * Add an implementation of FieldsetPopulatorInterface corresponding to the supplied form control type
     *
     * @param string $type
     */
    public function registerPopulator($type, FieldsetPopulatorInterface $fieldsetPopulator): void
    {
        $this->fieldsetPopulators[$type] = $fieldsetPopulator;
    }
}

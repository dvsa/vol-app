<?php

namespace Common\Service\Qa\DataTransformer;

class DataTransformerProvider
{
    /** @var array */
    private $dataTransformers = [];

    /**
     * Get the DataTransformerInterface instance corresponding to the specified slug, or null if there isn't one
     *
     * @param string $slug
     *
     * @return DataTransformerInterface|null
     */
    public function getTransformer($slug)
    {
        if (!isset($this->dataTransformers[$slug])) {
            return null;
        }

        return $this->dataTransformers[$slug];
    }

    /**
     * Register a DataTransformerInterface instance against a slug
     *
     * @param string $slug
     */
    public function registerTransformer($slug, DataTransformerInterface $dataTransformer): void
    {
        $this->dataTransformers[$slug] = $dataTransformer;
    }
}

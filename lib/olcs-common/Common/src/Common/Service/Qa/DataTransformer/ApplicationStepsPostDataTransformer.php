<?php

namespace Common\Service\Qa\DataTransformer;

class ApplicationStepsPostDataTransformer
{
    /**
     * Create service instance
     *
     *
     * @return ApplicationStepsPostDataTransformer
     */
    public function __construct(private DataTransformerProvider $dataTransformerProvider)
    {
    }

    /**
     * Convert fieldset post data from the frontend to a format suitable for backend submission
     *
     *
     * @return array
     */
    public function getTransformed(array $applicationSteps, array $applicationStepsPostData)
    {
        foreach ($applicationSteps as $applicationStep) {
            $fieldsetName = $applicationStep['fieldsetName'];

            $dataTransformer = $this->dataTransformerProvider->getTransformer($applicationStep['slug']);
            if ($dataTransformer) {
                $applicationStepsPostData[$fieldsetName] = $dataTransformer->getTransformed(
                    $applicationStepsPostData[$fieldsetName]
                );
            }
        }

        return $applicationStepsPostData;
    }
}

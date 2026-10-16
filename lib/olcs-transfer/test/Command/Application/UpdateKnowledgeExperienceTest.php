<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateKnowledgeExperience;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use PHPUnit\Framework\TestCase;

final class UpdateKnowledgeExperienceTest extends TestCase
{
    use CommandTest;

    #[\Override]
    protected function createBlankDto()
    {
        return new UpdateKnowledgeExperience();
    }

    #[\Override]
    protected function getOptionalDtoFields()
    {
        return ['evidenceUploadType'];
    }

    #[\Override]
    protected function getValidFieldValues()
    {
        return [
            'id' => ['1'],
            'version' => ['1'],
            'evidenceUploadType' => ['1', '2'],
            'knowledgeExperienceOlat' => ['Y', 'N'],
        ];
    }

    #[\Override]
    protected function getInvalidFieldValues()
    {
        return [
            'evidenceUploadType' => ['0', '3'],
            'knowledgeExperienceOlat' => ['X', ''],
        ];
    }

    /**
     * The frontend mapper sends the upload type as an int
     */
    #[\Override]
    protected function getFilterTransformations()
    {
        return [
            'evidenceUploadType' => [[1, '1'], [2, '2']],
            'knowledgeExperienceOlat' => [[' Y ', 'Y']],
        ];
    }
}

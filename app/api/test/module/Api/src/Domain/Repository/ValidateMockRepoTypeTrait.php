<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

trait ValidateMockRepoTypeTrait
{
    protected function validateMockRepoType(mixed $name, mixed $class): void
    {
        foreach (RepoTypeWhitelist::TYPES as $classOrInterfaceName) {
            if ($class instanceof $classOrInterfaceName) {
                return;
            }
        }

        $errorMessage = sprintf(
            'Class being mocked %s does not implement one of the types specified in REPOSITORY_TYPE_WHITELIST',
            $name
        );

        $this->fail($errorMessage);
    }
}

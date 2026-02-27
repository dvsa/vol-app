<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\OlcsTest\Builder\BuilderInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;

class ApplicationCompletionBuilder implements BuilderInterface
{
    /**
     * @var ApplicationCompletion
     */
    protected $instance;

    /**
     * @param int|null $id
     */
    public function __construct(Application $application, ?int $id = null)
    {
        $this->instance = new ApplicationCompletion($application);
        if (null !== $id) {
            $this->instance->setId($id);
        }
    }

    /**
     * @return $this
     */
    public function withUpdatedOperatingCentresSection(): self
    {
        $this->instance->setOperatingCentresStatus(ApplicationCompletion::STATUS_VARIATION_UPDATED);
        return $this;
    }

    /**
     * @return ApplicationCompletion
     */
    public function build(): ApplicationCompletion
    {
        return $this->instance;
    }

    /**
     * @param int|null $id
     * @return static
     */
    public static function forApplication(Application $application, ?int $id = null): self
    {
        return new static($application, $id);
    }
}

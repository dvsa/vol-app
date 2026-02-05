<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Organisation;

use Dvsa\OlcsTest\Builder\BuilderInterface;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

class OrganisationBuilder implements BuilderInterface
{
    /**
     * @var Organisation
     */
    protected $instance;

    /**
     * @param int|null $id
     */
    public function __construct(?int $id = null)
    {
        $this->instance = new Organisation();
        if ($id) {
            $this->instance->setId($id);
        }
    }

    /**
     * @return Organisation
     */
    public function build(): Organisation
    {
        return $this->instance;
    }

    /**
     * @return static
     */
    public static function anOrganisation(): self
    {
        return new static();
    }
}

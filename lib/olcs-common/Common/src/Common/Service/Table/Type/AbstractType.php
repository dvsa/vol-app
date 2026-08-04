<?php

namespace Common\Service\Table\Type;

use Common\Service\Table\TableBuilder;

abstract class AbstractType
{
    public function __construct(
        protected TableBuilder $table
    ) {
    }

    protected function getTable(): TableBuilder
    {
        return $this->table;
    }

    abstract public function render(array $data, array $column, string|null $formattedContent = null): array|string;
}

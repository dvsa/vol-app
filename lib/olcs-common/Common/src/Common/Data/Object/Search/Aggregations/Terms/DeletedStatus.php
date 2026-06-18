<?php

declare(strict_types=1);

namespace Common\Data\Object\Search\Aggregations\Terms;

class DeletedStatus extends TermsAbstract
{
    protected $title = 'search.form.filter.deleted-status';
    protected $key = 'personDeleted';

    #[\Override]
    public function getType(): string
    {
        return self::TYPE_BOOLEAN;
    }

    #[\Override]
    public function getOptionsKvp(): array
    {
        return $this->getOptions();
    }

    #[\Override]
    public function getOptions(): array
    {
        return [
            '1' => 'Show deleted',
            '0' => 'Hide deleted',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Common\Data\Object\Search\Aggregations\Terms;

class MergedStatus extends TermsAbstract
{
    protected $title = 'search.form.filter.merged-status';
    protected $key = 'isMerged';

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
            '1' => 'Show merged',
            '0' => 'Hide merged',
        ];
    }
}

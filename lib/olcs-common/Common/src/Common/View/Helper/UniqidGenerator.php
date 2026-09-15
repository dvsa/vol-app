<?php

namespace Common\View\Helper;

class UniqidGenerator
{
    protected $id;

    public function getLastId()
    {
        return $this->id;
    }

    public function generateId(string $prefix = '', bool $more_entropy = false): string
    {
        $this->id = uniqid($prefix, $more_entropy);
        return $this->id;
    }
}

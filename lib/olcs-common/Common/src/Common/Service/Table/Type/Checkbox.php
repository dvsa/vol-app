<?php

namespace Common\Service\Table\Type;

class Checkbox extends Selector
{
    protected string $format = '<input type="checkbox" name="%s[]" value="%s" %s />';
}

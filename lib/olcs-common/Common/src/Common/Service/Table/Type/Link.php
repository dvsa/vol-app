<?php

namespace Common\Service\Table\Type;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\Formatter\StackValueReplacer;

class Link extends AbstractType
{
    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): array|string
    {
        $params = $column['params'] ?? [];

        foreach ($params as $key => $param) {
            $params[$key] = (new StackValueReplacer(new StackValue(new StackHelperService())))->format($data, ['stringFormat' => $param]);
        }

        $url = $this->getTable()->getServiceLocator()->get('Helper\Url')->fromRoute($column['route'], $params);

        return str_replace('[LINK]', $url, $formattedContent);
    }
}

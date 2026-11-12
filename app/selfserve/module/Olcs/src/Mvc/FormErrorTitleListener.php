<?php

declare(strict_types=1);

namespace Olcs\Mvc;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Form\Form as LaminasForm;
use Laminas\Form\FormInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Model\ModelInterface;

class FormErrorTitleListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public function __construct(private Placeholder $placeholder)
    {
    }

    #[\Override]
    public function attach(EventManagerInterface $events, $priority = 0)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, $this->onRender(...), $priority);
    }

    public function onRender(MvcEvent $e): void
    {
        $model = $e->getResult();
        if ($model instanceof ModelInterface && $this->hasFormErrorsInModel($model)) {
            $this->placeholder->__invoke('formHasErrors')->set(true);
        }
    }

    private function hasFormErrorsInModel(ModelInterface $model): bool
    {
        if ($this->hasFormErrorsInValue($model->getVariables())) {
            return true;
        }

        foreach ($model->getChildren() as $child) {
            if ($this->hasFormErrorsInModel($child)) {
                return true;
            }
        }

        return false;
    }

    private function hasFormErrorsInValue(mixed $value): bool
    {
        if ($value instanceof FormInterface) {
            return $value->getMessages() !== []
                || ($value instanceof LaminasForm && $value->hasValidated() && !$value->isValid());
        }

        if ($value instanceof ModelInterface) {
            return $this->hasFormErrorsInModel($value);
        }

        if (is_iterable($value)) {
            foreach ($value as $item) {
                if ($this->hasFormErrorsInValue($item)) {
                    return true;
                }
            }
        }

        return false;
    }
}

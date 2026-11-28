<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Support;

use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;

final class EntityFieldReads
{
    public static function viaGetter(object $entity, string $field): mixed
    {
        foreach (['get', 'is', ''] as $prefix) {
            $method = $prefix . $field;
            if (method_exists($entity, $method)) {
                return $entity->$method();
            }
        }

        throw new \LogicException(sprintf('%s has no getter for %s', $entity::class, $field));
    }

    public static function stored(object $entity, string $field): mixed
    {
        return ClosureExpressionVisitor::getObjectFieldValue($entity, $field, true);
    }
}

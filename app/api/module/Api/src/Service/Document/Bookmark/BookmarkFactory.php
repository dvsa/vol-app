<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Laminas\Filter\Word\CamelCaseToUnderscore;
use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * Bookmark factory class
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BookmarkFactory
{
    public function getClassNameFromToken($token)
    {
        $c2u = new CamelCaseToUnderscore();
        $u2c = new UnderscoreToCamelCase();

        // 1) SomethingLike_This -> Something_Like_This
        $className = $c2u->filter($token);
        // 2) SOMETHING__Like_This -> something__like_this
        $className = strtolower((string) $className);
        // 3) something__like_this -> Something_LikeThis
        $className = $u2c->filter($className);
        // 4) Something_LikeThis -> SomethingLikeThis
        $className = str_replace("_", "", $className);

        return $className;
    }

    public function locate($token)
    {
        $className = $this->getClassNameFromToken($token);

        $instance = $this->getInstance($className);

        $instance->setToken($token);

        return $instance;
    }

    private function getInstance($className)
    {
        $class = __NAMESPACE__ . '\\' . $className;

        if (class_exists($class)) {
            /**
             * if we have a specific class to handle this bookmark then life's good,
             * we can just hand off straight to it
             */
            return new $class();
        }

        $alias = __NAMESPACE__ . '\Alias\\' . $className;

        if (class_exists($alias)) {
            /**
             * An alias is simply a bookmark which extends another without modifying its
             * behaviour in any way. They only exist because the bookmarks we're importing
             * have wildly differing names across different templates and trying to consolidate
             * them all at source is almost impossible
             */
            return new $alias();
        }

        /**
         * otherwise we fall back to a class which will rummage through the data
         * it is later provided looking for a known key representing user chosen
         * paragraphs
         */
        return new TextBlock();
    }
}

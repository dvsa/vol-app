<?php

namespace Common\Validator;

/**
 * @package Common\Validator
 */
class FileUploadCountV2 extends FileUploadCount
{
    /**
     * Is the value valid
     *
     * @param mixed $value   Value to validate
     * @param array $context Context data
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        // Expects to see an index "list" in context which is an array of uploaded files
        $count = (is_array($context['list'])) ? count($context['list']) : 0;
        if ($count >= $this->getMin()) {
            return true;
        }

        $this->error(self::TOO_FEW);

        return false;
    }
}

<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class ConvictionCommentBox
 * @package Olcs\Data\Mapper
 */
class ConvictionCommentBox extends AbstractCommentMapper implements MapperInterface
{
    public const COMMENT_FIELD = 'convictionNote'; //needs to be constant as methods called statically
}

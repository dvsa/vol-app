<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class ProhibitionCommentBox
 * @package Olcs\Data\Mapper
 */
class ProhibitionCommentBox extends AbstractCommentMapper implements MapperInterface
{
    const COMMENT_FIELD = 'prohibitionNote'; //needs to be constant as methods called statically
}

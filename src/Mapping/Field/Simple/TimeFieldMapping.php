<?php

namespace Saxulum\ModelGenerator\Mapping\Field\Simple;

use Saxulum\ModelGenerator\Mapping\Field\AbstractFieldMapping;

class TimeFieldMapping extends AbstractFieldMapping
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'time';
    }
}

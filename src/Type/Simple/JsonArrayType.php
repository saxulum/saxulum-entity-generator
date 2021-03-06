<?php

namespace Saxulum\EntityGenerator\Type\Simple;

use PhpParser\Node\Name;
use Saxulum\EntityGenerator\Mapping\FieldMappingInterface;

class JsonArrayType extends AbstractType
{
    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return null|string|Name
     */
    protected function getSetterType(FieldMappingInterface $fieldMapping)
    {
        return 'array';
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return string
     */
    public function getPhpDocType(FieldMappingInterface $fieldMapping)
    {
        return 'array';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'json_array';
    }
}

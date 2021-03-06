<?php

namespace Saxulum\EntityGenerator\Type\Simple;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use Saxulum\EntityGenerator\Mapping\FieldMappingInterface;

class DateTimeZType extends AbstractType
{
    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return null|Expr
     */
    protected function getSetterDefault(FieldMappingInterface $fieldMapping)
    {
        return new ConstFetch(new Name('null'));
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return null|string|Name
     */
    protected function getSetterType(FieldMappingInterface $fieldMapping)
    {
        return new Name('\DateTime');
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return string
     */
    public function getPhpDocType(FieldMappingInterface $fieldMapping)
    {
        return '\DateTime';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'datetimez';
    }
}

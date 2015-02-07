<?php

namespace Saxulum\ModelGenerator\Type\Relation;

use PhpParser\Node;
use Saxulum\ModelGenerator\Mapping\FieldMappingInterface;
use Saxulum\ModelGenerator\Mapping\Relation\AbstractRelationMapping;

abstract class AbstractOne2OneType extends Abstract2OneRelationType
{
    /**
     * @param  AbstractRelationMapping $fieldMapping
     * @return string
     */
    protected function getVarString(AbstractRelationMapping $fieldMapping)
    {
        return $fieldMapping->getTargetModel();
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node[]
     */
    public function getConstructNodes(FieldMappingInterface $fieldMapping)
    {
        return array();
    }
}

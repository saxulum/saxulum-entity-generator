<?php

namespace Saxulum\EntityGenerator\Type\Relation;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Saxulum\EntityGenerator\Mapping\FieldMappingInterface;
use Saxulum\EntityGenerator\Mapping\Relation\AbstractRelationMapping;
use Saxulum\EntityGenerator\Mapping\Relation\Many2OneMapping;
use Saxulum\PhpDocGenerator\Documentor;
use Saxulum\PhpDocGenerator\ParamRow;
use Saxulum\PhpDocGenerator\ReturnRow;
use Saxulum\EntityGenerator\Helper\StringUtil;

class Many2OneType extends Abstract2OneRelationType
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

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node[]
     */
    public function getMethodsNodes(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2OneMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2OneMapping!');
        }

        if (null === $inversedBy = $fieldMapping->getInversedBy()) {
            return array(
                $this->getUnidirectionalSetterMethodNode($fieldMapping),
                $this->getGetterMethodNode($fieldMapping->getName(), $fieldMapping->getTargetModel()),
            );
        }

        return array(
            $this->getBidiretionalSetterMethodNode($fieldMapping, StringUtil::singularify($inversedBy), 'remove', 'add'),
            $this->getGetterMethodNode($fieldMapping->getName(), $fieldMapping->getTargetModel()),
        );
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node
     */
    protected function getSetterMethodNode(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2OneMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2OneMapping!');
        }
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node
     */
    protected function getUnidirectionalSetterMethodNode(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2OneMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2OneMapping!');
        }

        $name = $fieldMapping->getName();
        $targetModel = $fieldMapping->getTargetModel();

        return new ClassMethod('set'.ucfirst($name),
            array(
                'type' => 1,
                'params' => array(
                    new Param($fieldMapping->getName(), new ConstFetch(new Name('null')), new Name($targetModel)),
                ),
                'stmts' => array(
                    new Assign(
                        new PropertyFetch(new Variable('this'), $name),
                        new Variable($name)
                    ),
                    new Return_(new Variable('this')),
                ),
            ),
            array(
                'comments' => array(
                    new Comment(
                        new Documentor(array(
                            new ParamRow($targetModel, $name),
                            new ReturnRow('$this'),
                        ))
                    ),
                ),
            )
        );
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node[]
     */
    public function getMetadataNodes(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2OneMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2OneMapping!');
        }

        if (null === $fieldMapping->getInversedBy()) {
            return array(
                new MethodCall(new Variable('builder'), 'addManyToOne', array(
                    new Arg(new String_($fieldMapping->getName())),
                    new Arg(new String_($fieldMapping->getTargetModel())),
                )),
            );
        }

        return array(
            new MethodCall(new Variable('builder'), 'addManyToOne', array(
                new Arg(new String_($fieldMapping->getName())),
                new Arg(new String_($fieldMapping->getTargetModel())),
                new Arg(new String_($fieldMapping->getInversedBy())),
            )),
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'many2one';
    }
}

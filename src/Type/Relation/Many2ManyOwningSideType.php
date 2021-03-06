<?php

namespace Saxulum\EntityGenerator\Type\Relation;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Saxulum\EntityGenerator\Mapping\FieldMappingInterface;
use Saxulum\EntityGenerator\Mapping\Relation\Many2ManyOwningSideMapping;
use Saxulum\PhpDocGenerator\Documentor;
use Saxulum\PhpDocGenerator\ParamRow;
use Saxulum\PhpDocGenerator\ReturnRow;
use Saxulum\EntityGenerator\Helper\StringUtil;

class Many2ManyOwningSideType extends AbstractMany2ManyType
{
    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node[]
     */
    public function getMethodsNodes(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2ManyOwningSideMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2ManyOwningSideMapping!');
        }

        if (null === $inversedBy = $fieldMapping->getInversedBy()) {
            return array(
                $this->getUnidirectionalAddMethodNode($fieldMapping),
                $this->getUnidirectionalRemoveMethodNode($fieldMapping),
                $this->getUnidirectionalSetterMethodNode($fieldMapping),
                $this->getGetterMethodNode(
                    $fieldMapping->getName(),
                    $fieldMapping->getTargetModel().'[]|\Doctrine\Common\Collections\Collection'
                ),
            );
        }

        return array(
            $this->getBidiretionalAddMethodNode($fieldMapping, $inversedBy),
            $this->getBidiretionalRemoveMethodNode($fieldMapping, $inversedBy),
            $this->getBidiretionalSetterMethodNode($fieldMapping),
            $this->getGetterMethodNode(
                $fieldMapping->getName(),
                $fieldMapping->getTargetModel().'[]|\Doctrine\Common\Collections\Collection'
            ),
        );
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node
     */
    protected function getUnidirectionalAddMethodNode(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2ManyOwningSideMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2ManyOwningSideMapping!');
        }

        $name = $fieldMapping->getName();
        $singularName = StringUtil::singularify($name);
        $targetModel = $fieldMapping->getTargetModel();

        return new ClassMethod('add'.ucfirst($singularName),
            array(
                'type' => 1,
                'params' => array(
                    new Param($singularName, null, new Name($targetModel)),
                ),
                'stmts' => array(
                    new MethodCall(
                        new PropertyFetch(new Variable('this'), $name),
                        'add',
                        array(
                            new Arg(new Variable($singularName)),
                        )
                    ),
                    new Return_(new Variable('this')),
                ),
            ),
            array(
                'comments' => array(
                    new Comment(
                        new Documentor(array(
                            new ParamRow($targetModel, $singularName),
                            new ReturnRow('$this'),
                        ))
                    ),
                ),
            )
        );
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node
     */
    protected function getUnidirectionalRemoveMethodNode(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2ManyOwningSideMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2ManyOwningSideMapping!');
        }

        $name = $fieldMapping->getName();
        $singularName = StringUtil::singularify($name);
        $targetModel = $fieldMapping->getTargetModel();

        return new ClassMethod('remove'.ucfirst($singularName),
            array(
                'type' => 1,
                'params' => array(
                    new Param($singularName, null, new Name($targetModel)),
                ),
                'stmts' => array(
                    new MethodCall(
                        new PropertyFetch(new Variable('this'), $name),
                        'removeElement',
                        array(
                            new Arg(new Variable($singularName)),
                        )
                    ),
                    new Return_(new Variable('this')),
                ),
            ),
            array(
                'comments' => array(
                    new Comment(
                        new Documentor(array(
                            new ParamRow($targetModel, $singularName),
                            new ReturnRow('$this'),
                        ))
                    ),
                ),
            )
        );
    }

    /**
     * @param  FieldMappingInterface $fieldMapping
     * @return Node
     */
    protected function getUnidirectionalSetterMethodNode(FieldMappingInterface $fieldMapping)
    {
        if (!$fieldMapping instanceof Many2ManyOwningSideMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2ManyOwningSideMapping!');
        }

        $name = $fieldMapping->getName();
        $targetModel = $fieldMapping->getTargetModel();

        return new ClassMethod('set'.ucfirst($name),
            array(
                'type' => 1,
                'params' => array(
                    new Param($fieldMapping->getName()),
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
                            new ParamRow($targetModel.'[]|\Doctrine\Common\Collections\Collection', $name),
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
        if (!$fieldMapping instanceof Many2ManyOwningSideMapping) {
            throw new \InvalidArgumentException('Field mapping has to be Many2ManyOwningSideMapping!');
        }

        if (null === $fieldMapping->getInversedBy()) {
            return array(
                new MethodCall(new Variable('builder'), 'addOwningManyToMany', array(
                    new Arg(new String_($fieldMapping->getName())),
                    new Arg(new String_($fieldMapping->getTargetModel())),
                )),
            );
        }

        return array(
            new MethodCall(new Variable('builder'), 'addOwningManyToMany', array(
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
        return 'many2many-owningside';
    }
}

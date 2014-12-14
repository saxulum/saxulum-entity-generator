<?php

namespace Saxulum\ModelGenerator\DoctrineOrm;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\PrettyPrinter\Standard as PhpGenerator;
use Saxulum\ModelGenerator\GeneratorInterface;
use Saxulum\ModelGenerator\Mapping\ModelMapping;
use Saxulum\ModelGenerator\PhpDoc\Documentor;
use Saxulum\ModelGenerator\PhpDoc\ParamRow;

class Generator implements GeneratorInterface
{
    const NAMESPACE_PART = 'Entity';
    const CLASS_ORM_METADATA = '\Doctrine\ORM\Mapping\ClassMetadata';
    const CLASS_ORM_METADATA_BUILDER = '\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder';

    /**
     * @var PhpGenerator
     */
    protected $phpGenerator;

    /**
     * @var TypeInterface[]
     */
    protected $types;

    /**
     * @param PhpGenerator $phpGenerator
     * @param TypeInterface[] $types
     */
    public function __construct(PhpGenerator $phpGenerator, array $types)
    {
        $this->phpGenerator = $phpGenerator;

        foreach ($types as $type) {
            if (!$type instanceof TypeInterface) {
                throw new \InvalidArgumentException("Type is not an instance of TypeInterface!");
            }

            $this->types[$type->getName()] = $type;
        }
    }

    /**
     * @param ModelMapping $modelMapping
     */
    public function generate(ModelMapping $modelMapping)
    {
        $baseNamespace = $modelMapping->getBaseNamespace() . '\\' . static::NAMESPACE_PART;
        $basePath = $modelMapping->getBasePath() . DIRECTORY_SEPARATOR . static::NAMESPACE_PART;
        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }


        $baseClassPath = $basePath . DIRECTORY_SEPARATOR . 'Abstract' . $modelMapping->getName() . '.php';

        $nodes = array();
        $nodes = array_merge($nodes, $this->generatePropertyNodes($modelMapping));
        $nodes = array_merge($nodes, $this->generateMethodNodes($modelMapping));
        $nodes = array_merge($nodes, $this->generateDoctrineOrmMetadataNodes($modelMapping));
        $nodes = array(
            new Node\Stmt\Namespace_(new Name($baseNamespace), array(
                new Class_('Abstract' . $modelMapping->getName(), array('type' => 16, 'stmts' => $nodes)))
            )
        );
        $baseClassCode = $this->phpGenerator->prettyPrint($nodes);

        file_put_contents($baseClassPath, '<?php' . PHP_EOL . PHP_EOL . $baseClassCode);
    }

    /**
     * @param ModelMapping $modelMapping
     * @return Node[]
     * @throws \Exception
     */
    protected function generatePropertyNodes(ModelMapping $modelMapping)
    {
        return $this->generateNodes($modelMapping, 'getDoctrineOrmPropertyNodes');
    }

    /**
     * @param ModelMapping $modelMapping
     * @return Node[]
     * @throws \Exception
     */
    protected function generateMethodNodes(ModelMapping $modelMapping)
    {
        return $this->generateNodes($modelMapping, 'getDoctrineOrmMethodNodes');
    }

    /**
     * @param ModelMapping $modelMapping
     * @return Node[]
     * @throws \Exception
     */
    protected function generateDoctrineOrmMetadataNodes(ModelMapping $modelMapping)
    {
        return array(
            new ClassMethod('loadMetadata',
                array(
                    'type' => 9,
                    'params' => array(
                        new Param('metadata', null, new Name(static::CLASS_ORM_METADATA))
                    ),
                    'stmts' => array_merge(
                        array(
                            new Assign(new Variable('builder'), new New_(new Name(static::CLASS_ORM_METADATA_BUILDER), array(
                                new Arg(new Variable('metadata'))
                            ))),
                            new MethodCall(new Variable('builder'), 'setMappedSuperClass')
                        ),
                        $this->generateNodes($modelMapping, 'getDoctrineOrmMetadataNodes')
                    )
                ),
                array(
                    'comments' => array(
                        new Comment(
                            new Documentor(array(
                                new ParamRow(static::CLASS_ORM_METADATA, 'metadata')
                            ))
                        )
                    )
                )
            )
        );
    }

    /**
     * @param ModelMapping $modelMapping
     * @param  string            $getterName
     * @return Node[]
     * @throws \Exception
     */
    protected function generateNodes(ModelMapping $modelMapping, $getterName)
    {
        $fieldNodes = array();
        foreach ($modelMapping->getFieldMappings() as $fieldMapping) {
            $type = $this->getType($fieldMapping->getType());
            if (null === $type) {
                throw new \Exception("Unknown type: {$fieldMapping->getType()}!");
            }
            if (!is_callable(array($type, $getterName))) {
                throw new \Exception("Can't call {$fieldMapping->getType()} method {$getterName}!");
            }

            $fieldNodes = array_merge($fieldNodes, $type->$getterName($fieldMapping->getName()));
        }

        return $fieldNodes;
    }

    /**
     * @param $type
     * @return null|TypeInterface
     */
    protected function getType($type)
    {
        return isset($this->types[$type]) ? $this->types[$type] : null;
    }
}
<?php

namespace Saxulum\EntityGenerator\Mapping\Simple;

use Saxulum\EntityGenerator\Mapping\AbstractFieldMapping;

class ObjectFieldMapping extends AbstractFieldMapping
{
    /**
     * @var string
     */
    protected $class;

    public function __construct($name, $class)
    {
        parent::__construct($name);
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'object';
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}

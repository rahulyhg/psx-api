<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PSX\Api\Generator;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;
use PSX\Api\GeneratorAbstract;
use PSX\Api\Resource;
use PSX\Schema\Property;
use PSX\Schema\Builder;
use PSX\Schema\PropertyInterface;
use PSX\Schema\Generator;
use PSX\Schema\SchemaInterface;

/**
 * Php
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Php extends GeneratorAbstract
{
    use Generator\GeneratorTrait;

    /**
     * @var \PhpParser\BuilderFactory
     */
    protected $factory;

    /**
     * @var \PhpParser\PrettyPrinter\Standard
     */
    protected $printer;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @param string|null $namespace
     */
    public function __construct($namespace = null)
    {
        $this->factory   = new BuilderFactory();
        $this->printer   = new PrettyPrinter\Standard();
        $this->namespace = $namespace === null ? 'PSX\Generation' : $namespace;
    }

    /**
     * @param \PSX\Api\Resource $resource
     * @return string
     */
    public function generate(Resource $resource)
    {
        $root      = $this->factory->namespace($this->namespace);
        $className = 'Endpoint';
        $methods   = $resource->getMethods();

        $class = $this->factory->class($className);
        $class->extend('SchemaApiAbstract');
        $class->setDocComment($this->getDocCommentForResource($resource));

        foreach ($methods as $methodName => $method) {
            $class->addStmt($this->factory->method('do' . ucfirst(strtolower($methodName)))
                ->makePublic()
                ->setDocComment($this->getDocCommentForMethod($method))
                ->addParam($this->factory->param('record'))
            );
        }

        $root->addStmt($this->factory->use('PSX\Framework\Controller\SchemaApiAbstract'));
        $root->addStmt($class->getNode());

        return $this->printer->prettyPrintFile([$root->getNode()]);
    }

    /**
     * @param \PSX\Api\Resource $resource
     * @return string
     */
    protected function getDocCommentForResource(Resource $resource)
    {
        $comment = '/**' . "\n";

        $title = $resource->getTitle();
        if (!empty($title)) {
            $comment.= ' * @Title("' . $this->escapeString($title) . '")' . "\n";
        }

        $description = $resource->getDescription();
        if (!empty($description)) {
            $comment.= ' * @Description("' . $this->escapeString($description) . '")' . "\n";
        }

        $pathParameters = $resource->getPathParameters();
        $properties     = $pathParameters->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $name => $parameter) {
                $comment.= ' * @PathParam(' . $this->getParam($name, $parameter, in_array($name, $pathParameters->getRequired())) . ')' . "\n";
            }
        }

        $comment.= ' */';

        return $comment;
    }

    /**
     * @param \PSX\Api\Resource\MethodAbstract $method
     * @return string
     */
    protected function getDocCommentForMethod(Resource\MethodAbstract $method)
    {
        $comment = '/**' . "\n";

        $description = $method->getDescription();
        if (!empty($description)) {
            $comment.= ' * @Description("' . $this->escapeString($description) . '")' . "\n";
        }

        $queryParameters = $method->getQueryParameters();
        $properties      = $queryParameters->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $name => $parameter) {
                $comment.= ' * @QueryParam(' . $this->getParam($name, $parameter, in_array($name, $queryParameters->getRequired())) . ')' . "\n";
            }
        }

        $request = $method->getRequest();
        if ($request instanceof SchemaInterface) {
            $class   = $this->getClassNameForProperty($request->getDefinition());
            $comment.= ' * @Incoming(schema="' . $class . '")' . "\n";
        }

        $responses = $method->getResponses();
        foreach ($responses as $statusCode => $response) {
            if ($response instanceof SchemaInterface) {
                $class   = $this->getClassNameForProperty($response->getDefinition());
                $comment.= ' * @Outgoing(code=' . $statusCode . ', schema="' . $class . '")' . "\n";
            }
        }

        $comment.= ' */';

        return $comment;
    }

    /**
     * @param string $name
     * @param \PSX\Schema\PropertyInterface $property
     * @param boolean $required
     * @return string
     */
    protected function getParam($name, PropertyInterface $property, $required)
    {
        $attributes = [
            'name' => '"' . $this->escapeString($name) . '"'
        ];

        $type = $property->getType();
        if (!empty($type)) {
            $attributes['description'] = '"' . $this->escapeString($type) . '"';
        }

        $description = $property->getDescription();
        if (!empty($description)) {
            $attributes['description'] = '"' . $this->escapeString($description) . '"';
        }

        if ($required) {
            $attributes['required'] = 'true';
        }

        $enum = $property->getEnum();
        if (!empty($enum)) {
            $vals = [];
            foreach ($enum as $value) {
                $vals[] = '"' . $this->escapeString($value) . '"';
            }
            $attributes['enum'] = '{' . implode(', ', $vals) . '}';
        }

        // string
        $minLength = $property->getMinLength();
        if ($minLength !== null) {
            $attributes['minLength'] = (int) $minLength;
        }

        $maxLength = $property->getMaxLength();
        if ($maxLength !== null) {
            $attributes['maxLength'] = (int) $maxLength;
        }

        $pattern = $property->getPattern();
        if (!empty($pattern)) {
            $attributes['pattern'] = '"' . $this->escapeString($pattern) . '"';
        }

        $format = $property->getFormat();
        if (!empty($format)) {
            $attributes['format'] = '"' . $this->escapeString($format) . '"';
        }

        // number
        $minimum = $property->getMinimum();
        if ($minimum !== null) {
            $attributes['minimum'] = (int) $minimum;
        }

        $maximum = $property->getMaximum();
        if ($maximum !== null) {
            $attributes['maximum'] = (int) $maximum;
        }

        $multipleOf = $property->getMultipleOf();
        if ($multipleOf !== null) {
            $attributes['multipleOf'] = (int) $multipleOf;
        }

        $param = [];
        foreach ($attributes as $name => $value) {
            $param[] = $name . '=' . $value;
        }

        return implode(', ', $param);
    }

    /**
     * @param \PSX\Schema\PropertyInterface $property
     * @return string
     */
    protected function getClassNameForProperty(PropertyInterface $property)
    {
        return $this->namespace . '\\' . $this->getIdentifierForProperty($property);
    }

    /**
     * @param string $data
     * @return string
     */
    protected function escapeString($data)
    {
        return $data;
    }
}

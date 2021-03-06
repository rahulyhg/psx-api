<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Api\Tests;

use PHPUnit\Framework\TestCase;
use PSX\Api\GeneratorFactory;
use PSX\Api\GeneratorFactoryInterface;
use PSX\Api\GeneratorInterface;

/**
 * GeneratorFactoryTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class GeneratorFactoryTest extends TestCase
{
    /**
     * @dataProvider typeProvider
     */
    public function testGetGenerator($type)
    {
        $schemaReader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
        $schemaReader->addNamespace('PSX\\Schema\\Parser\\Popo\\Annotation');

        $factory   = new GeneratorFactory($schemaReader, 'urn:phpsx.org:2016#', 'http://foo.com', '');
        $generator = $factory->getGenerator($type);

        $this->assertInstanceOf(GeneratorInterface::class, $generator);
    }

    public function typeProvider()
    {
        return [
            [GeneratorFactoryInterface::TYPE_HTML],
            [GeneratorFactoryInterface::TYPE_JSONSCHEMA],
            [GeneratorFactoryInterface::TYPE_MARKDOWN],
            [GeneratorFactoryInterface::TYPE_OPENAPI],
            [GeneratorFactoryInterface::TYPE_PHP],
            [GeneratorFactoryInterface::TYPE_RAML],
            [GeneratorFactoryInterface::TYPE_SERIALIZE],
            [GeneratorFactoryInterface::TYPE_SWAGGER],
            [GeneratorFactoryInterface::TYPE_TEMPLATE],
            [GeneratorFactoryInterface::TYPE_TYPESCRIPT],
        ];
    }
}

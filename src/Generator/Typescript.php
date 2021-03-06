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

namespace PSX\Api\Generator;

use PSX\Schema;
use PSX\Schema\GeneratorInterface;
use PSX\Schema\PropertyType;

/**
 * Typescript
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Typescript extends LanguageAbstract
{
    /**
     * @inheritdoc
     */
    protected function getType(PropertyType $property): string
    {
        if ($property->getType() == PropertyType::TYPE_STRING) {
            return 'string';
        } elseif ($property->getType() == PropertyType::TYPE_NUMBER || $property->getType() == PropertyType::TYPE_INTEGER) {
            return 'number';
        } elseif ($property->getType() == PropertyType::TYPE_BOOLEAN) {
            return 'boolean';
        } else {
            return 'any';
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate(): string
    {
        return 'typescript.ts.twig';
    }

    /**
     * @inheritdoc
     */
    protected function getGenerator(): GeneratorInterface
    {
        return new Schema\Generator\Typescript();
    }
}

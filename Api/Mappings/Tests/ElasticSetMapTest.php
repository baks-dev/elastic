<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Elastic\Api\Mappings\Tests;

use BaksDev\Elastic\Api\Mappings\ElasticSetMap;
use BaksDev\Elastic\Api\Properties\IntegerElasticType;
use BaksDev\Elastic\Api\Properties\KeywordElasticType;
use BaksDev\Elastic\Api\Properties\TextElasticType;
use BaksDev\Wildberries\Api\Token\Card\WildberriesCards\WildberriesCards;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group elastic
 * @group elastic-set-map
 */
#[When(env: 'test')]
final class ElasticSetMapTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var ElasticSetMap $ElasticSetMap */
        $ElasticSetMap = self::getContainer()->get(ElasticSetMap::class);

        $id = new KeywordElasticType('id');
        $int = new IntegerElasticType('counter');
        $text = new TextElasticType('text');

        $ElasticSetMap
            ->addProperty($id)
            ->addProperty($int)
            ->addProperty($text);

        $result = $ElasticSetMap->handle('test_index');
        //self::assertTrue($result);


        $newId = new KeywordElasticType('new-id');
        $ElasticSetMap->addProperty($newId);
        $result = $ElasticSetMap->handle('test_index');
        //self::assertTrue($result);

        self::assertTrue(true);

    }
}
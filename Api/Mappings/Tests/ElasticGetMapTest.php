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

use BaksDev\Elastic\Api\Mappings\ElasticGetMap;
use BaksDev\Elastic\Api\Mappings\ElasticSetMap;
use BaksDev\Elastic\Api\Properties\IntegerElasticType;
use BaksDev\Elastic\Api\Properties\KeywordElasticType;
use BaksDev\Elastic\Api\Properties\TextElasticType;
use BaksDev\Wildberries\Api\Token\Card\WildberriesCards\WildberriesCards;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group elastic
 * @group elastic-get-map
 *
 * depends BaksDev\Elastic\Api\Mappings\Tests\ElasticSetMapTest::class
 */
#[When(env: 'test')]
final class ElasticGetMapTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var ElasticGetMap $ElasticGetMap */
        $ElasticGetMap = self::getContainer()->get(ElasticGetMap::class);

        $result = $ElasticGetMap->handle('test_index');


        if($result)
        {
            self::assertTrue(isset($result['counter']));
            self::assertTrue(isset($result['counter']['type']));

            self::assertTrue(isset($result['id']));
            self::assertTrue(isset($result['id']['type']));

            self::assertTrue(isset($result['new-id']));
            self::assertTrue(isset($result['new-id']['type']));

            self::assertTrue(isset($result['text']));
            self::assertTrue(isset($result['text']['type']));
        }
        else
        {
            self::assertFalse($result);
        }

        self::assertTrue(true);

    }
}
<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Elastic\Api\Index;

use BaksDev\Elastic\Api\ElasticClient;
use Doctrine\ORM\Mapping\Table;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;

final class ElasticSetIndex extends ElasticClient
{
    public function handle(string $index, array $data): bool
    {
        if(empty($data))
        {
            return false;
        }

        if(!isset($data['id']))
        {
            throw new InvalidArgumentException('Идентификатор не найден');
        }

        if(!isset($data['text']))
        {
            throw new InvalidArgumentException('Текст поискового запроса не найден');
        }

        if(empty($data['text']))
        {
            throw new InvalidArgumentException('Текст поискового запроса не найден');
        }

        /**  Если передан класс сущности - определяем её таблицу */
        if(class_exists($index))
        {
            $ref = new ReflectionClass($index);
            /** @var ReflectionAttribute $current */
            $current = current($ref->getAttributes(Table::class));
            $index = $current->getArguments()['name'] ?? $index;
        }


//        $queryData = [
//            "id" => '018c53e6-e1a9-77e5-8e08-3a89377a3ca7',
//            "text" => 'Triangle '.uniqid('', true).' PL'.rand(100, 999).' - зимние автомобильные нешипованные шины',
//            //"text" => 'Triangle 12345 PL12345 - зимние автомобильные нешипованные шины',
//        ];

//        /** Удаляем индекс по идентификатору, если таковой имеется */
//        $deleteData['query']['match_phrase'] = ['id' => $data['id']];
//
//        $this->request('POST',
//            '/'.$index.'/_delete_by_query',
//            ['json' => $deleteData]
//        );

        /** Создаем индекс новый */
        $request = $this->request('POST', '/'.$index.'/_doc',
            ['json' => $data]
        );

        $response = $request->toArray(false);

        if($request->getStatusCode() !== 201)
        {
            $this->logger->critical(__FILE__.':'.__LINE__, $response);
            return false;
        }

        $this->logger->info(__FILE__.':'.__LINE__, $response);
        return true;
    }
}
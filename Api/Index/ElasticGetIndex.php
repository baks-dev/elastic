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

use App\Kernel;
use BaksDev\Elastic\Api\ElasticClient;
use Doctrine\ORM\Mapping\Table;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\TransportException;

final class ElasticGetIndex extends ElasticClient
{
    public function handle(string $index, ?string $search = null, int $definition = 0): bool|array
    {
        if(empty($search) || Kernel::isTestEnvironment())
        {
            return false;
        }

        /**  Если передан класс сущности - определяем её таблицу */
        if(class_exists($index))
        {
            $ref = new ReflectionClass($index);
            /** @var ReflectionAttribute $current */
            $current = current($ref->getAttributes(Table::class));
            $index = $current->getArguments()['name'] ?? $index;
        }


        $searchData["query"]["match"]['text']['query'] = $search;
        $searchData["query"]["match"]['text']["operator"] = "and";

        $searchData["query"]["match"]['text']["fuzziness"] = match ($definition)
        {
            1 => 1,
            2 => 2,
            default => 0,
        };

        $searchData["size"] = 5;

        try
        {
            $request = $this->request(
                'GET',
                '/'.$index.'/_search',
                ['json' => $searchData],
            );

            $response = $request->toArray(false);
        }
        catch(Exception)
        {
            return false;
        }

        if($request->getStatusCode() !== 200)
        {
            try
            {
                $content = $request->getContent();
                $this->logger->critical(sprintf('Status %s', $request->getStatusCode()), [self::class.':'.__LINE__]);
            }
            catch(ClientException $clientException)
            {
                $this->logger->critical($clientException->getMessage(), [self::class.':'.__LINE__]);
            }

            return false;
        }


        $this->logger->info(self::class.':'.__LINE__, $response);

        return $response;

    }
}

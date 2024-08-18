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

namespace BaksDev\Elastic\Messenger\ElasticReindex;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Elastic\Api\Index\ElasticDeleteIndex;
use BaksDev\Elastic\Api\Index\ElasticSetIndex;
use BaksDev\Elastic\Api\Mappings\ElasticSetMap;
use BaksDev\Elastic\Api\Properties\KeywordElasticType;
use BaksDev\Elastic\Api\Properties\TextElasticType;
use BaksDev\Elastic\Index\ElasticIndexInterface;
use DateInterval;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ElasticReindexHandler
{
    private iterable $elasticIndex;
    private ElasticSetMap $elasticSetMap;
    private ElasticSetIndex $elasticSetIndex;
    private ElasticDeleteIndex $elasticDeleteIndex;
    private LoggerInterface $logger;
    private AppCacheInterface $cache;

    public function __construct(
        LoggerInterface $elasticLogger,
        ElasticSetMap $elasticSetMap,
        ElasticSetIndex $elasticSetIndex,
        ElasticDeleteIndex $elasticDeleteIndex,
        AppCacheInterface $cache,
        #[AutowireIterator('baks.elastic.index')] iterable $elasticIndex
    )
    {
        $this->elasticIndex = $elasticIndex;
        $this->elasticSetMap = $elasticSetMap;
        $this->elasticSetIndex = $elasticSetIndex;
        $this->elasticDeleteIndex = $elasticDeleteIndex;
        $this->logger = $elasticLogger;
        $this->cache = $cache;
    }

    public function __invoke(ElasticReindexMessage $message): void
    {
        /**
         * Делаем проверку, нет ли запущенных процессов переиндексации
         */
        $cache = $this->cache->init('elastic');
        $item = $cache->getItem('reindex');

        if($item->isHit())
        {
            return;
        }

        $item->set(true);
        $item->expiresAfter(DateInterval::createFromDateString('5 minutes'));
        $cache->save($item);


        /** Запускаем переиндексацию */
        /** @var ElasticIndexInterface $index */
        foreach($this->elasticIndex as $index)
        {
            if(empty($index->getIndex()) || empty($index->getData()))
            {
                continue;
            }

            if($this->clear($index))
            {
                /** Создаем схему индекса */
                $id = new KeywordElasticType('id');
                $text = new TextElasticType('text');

                $this
                    ->elasticSetMap
                    ->addProperty($id)
                    ->addProperty($text)
                    ->handle($index->getIndex());

                foreach($index->getData() as $id => $text)
                {
                    $this->elasticSetIndex->handle($index->getIndex(),
                        [
                            'id' => $id,
                            'text' => $this->filterText($text)
                        ]);
                }
            }
        }

        $cache->deleteItem('reindex');
    }

    /**
     * Метод удаляет все ключи из таблицы индексов
     */
    public function clear(ElasticIndexInterface $index): bool
    {
        try
        {
            $this->elasticDeleteIndex->handle($index->getIndex());

        }
        catch(Exception $exception)
        {
            $this->logger->critical($exception->getMessage());
            return false;
        }

        return true;
    }


    /**
     * Метод фильтрует текст поиска, и составляет ключи
     */
    public function filterText(string|array $filter): string
    {
        if(is_array($filter))
        {
            $filter = implode(' ', $filter);
        }

        $filter = str_replace(['%PRODUCT_NAME%', '%PRODUCT_OFFER%', '%PRODUCT_VARIATION%', '%PRODUCT_MOD%'], '', $filter);

        $filter = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $filter);
        $filter = explode(' ', $filter);
        $filter = array_unique($filter);
        $filter = array_filter($filter);

        return implode(' ', $filter);
    }
}
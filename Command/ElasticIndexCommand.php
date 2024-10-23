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

namespace BaksDev\Elastic\Command;

use BaksDev\Elastic\Api\Index\ElasticDeleteIndex;
use BaksDev\Elastic\Api\Index\ElasticSetIndex;
use BaksDev\Elastic\Api\Mappings\ElasticSetMap;
use BaksDev\Elastic\Api\Properties\KeywordElasticType;
use BaksDev\Elastic\Api\Properties\TextElasticType;
use BaksDev\Elastic\Index\ElasticIndexInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsCommand(
    name: 'baks:elastic:index',
    description: 'Добавляет в поиск индексы',
)
]
class ElasticIndexCommand extends Command
{
    private iterable $elasticIndex;
    private ElasticSetMap $elasticSetMap;
    private ElasticSetIndex $elasticSetIndex;
    private ElasticDeleteIndex $elasticDeleteIndex;

    public function __construct(
        ElasticSetMap $elasticSetMap,
        ElasticSetIndex $elasticSetIndex,
        ElasticDeleteIndex $elasticDeleteIndex,
        #[AutowireIterator('baks.elastic.index')] iterable $elasticIndex,
    )
    {
        parent::__construct();

        $this->elasticIndex = $elasticIndex;
        $this->elasticSetMap = $elasticSetMap;
        $this->elasticSetIndex = $elasticSetIndex;
        $this->elasticDeleteIndex = $elasticDeleteIndex;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $progressBar = new ProgressBar($output);

        $isStart = false;

        /** @var ElasticIndexInterface $index */
        foreach($this->elasticIndex as $index)
        {


            if(empty($index->getIndex()) || empty($index->getData()))
            {
                continue;
            }


            try
            {
                $this->elasticDeleteIndex->handle($index->getIndex());

            }
            catch(Exception $exception)
            {
                $io->error($exception->getMessage());
                return Command::FAILURE;
            }

            if($isStart === false)
            {
                $progressBar->start();
            }

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
                $this->elasticSetIndex->handle($index->getIndex(), ['id' => $id, 'text' => $this->filterText($text)]);

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $io->success('Success Elastic Command');

        return Command::SUCCESS;

    }

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

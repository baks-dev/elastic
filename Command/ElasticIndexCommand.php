<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand(
    name: 'baks:elastic:index',
    description: 'Добавляет в поиск индексы')
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
        #[TaggedIterator('baks.elastic.index')] iterable $elasticIndex
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

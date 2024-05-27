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
use BaksDev\Elastic\Api\Mappings\ElasticGetMap;
use BaksDev\Elastic\Api\Mappings\ElasticSetMap;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'baks:elastic:index',
    description: 'Добавляет в поиск индексы')
]
class ElasticCommand extends Command
{
    public function __construct(
        ElasticGetMap $elasticGetMap,
        ElasticSetMap $elasticSetMap,
        ElasticSetIndex $elasticSetIndex,
        ElasticDeleteIndex $elasticDeleteIndex
    )
    {
        parent::__construct();

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('Success Elastic Command');

        return Command::SUCCESS;

    }

}

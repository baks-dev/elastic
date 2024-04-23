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

namespace BaksDev\Elastic\Messenger\Products;

use BaksDev\Elastic\BaksDevElasticBundle;
use BaksDev\Products\Product\Messenger\ProductMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;

#[AsMessageHandler]
final class ProductUpdateIndex
{
    private string $project_dir;

    public function __construct(#[Autowire('%kernel.project_dir%')] string $project_dir,)
    {
        $this->project_dir = $project_dir;
    }

    /**
     * Запускаем переиндексацию после обновления продукции
     */
    public function __invoke(ProductMessage $message): void
    {
        if(!class_exists(BaksDevElasticBundle::class))
        {
            return;
        }

        $process = Process::fromShellCommandline('ps aux | grep php | grep baks:elastic:index');

        $process->setTimeout(1);
        $process->run();

        $result = $process->getIterator($process::ITER_SKIP_ERR | $process::ITER_KEEP_OUTPUT)->current();
        $isRunning = (!empty($result) && strripos($result, 'console baks:elastic:index'));

        if(!$isRunning)
        {
            $elasticProcess = Process::fromShellCommandline('php '.$this->project_dir.'/bin/console baks:elastic:index');
            $elasticProcess->start();
        }
    }
}

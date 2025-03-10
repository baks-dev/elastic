<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Elastic\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class ElasticClient
{
    public function __construct(
        #[Autowire(env: 'ELASTIC_HOST')] private readonly string $host,
        #[Autowire(env: 'ELASTIC_PORT')] private readonly string $port,
        #[Autowire(env: 'ELASTIC_PASSWORD')] private readonly string $pass,
        #[Target('elasticLogger')] protected readonly LoggerInterface $logger

    ) {}

    protected function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $headers['accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';

        $HttpClient = HttpClient::create(['headers' => $headers])->withOptions([
            'base_uri' => 'https://'.$this->host.':'.$this->port,
            'auth_basic' => ['elastic', $this->pass],
            'verify_host' => false,
            'verify_peer' => false

        ]);

        $this->logger->info($method.' '.$url, $options);

        return $HttpClient->request($method, $url, $options);
    }
}
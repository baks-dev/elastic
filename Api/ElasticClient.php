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

namespace BaksDev\Elastic\Api;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Repository\WbTokenByProfile\WbTokenByProfileInterface;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

abstract class ElasticClient
{
    private string $host;
    private string $port;
    private string $pass;
    private string $cert;

    protected LoggerInterface $logger;

    public function __construct(
        #[Autowire(env: 'ELASTIC_HOST')] string $host,
        #[Autowire(env: 'ELASTIC_PORT')] string $port,
        #[Autowire(env: 'ELASTIC_PASSWORD')] string $pass,
        #[Autowire(env: 'ELASTIC_CERT')] string $cert,

        LoggerInterface $elasticLogger
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->pass = $pass;
        $this->cert = $cert;

        $this->logger = $elasticLogger;
    }

    protected function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $headers['accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';

        $HttpClient = HttpClient::create(['headers' => $headers])->withOptions([
            'base_uri' => 'https://'.$this->host.':'.$this->port,
            'auth_basic' => ['elastic', $this->pass],
            //'cafile' => $this->cert
            'verify_host' => false,
            'verify_peer' => false

        ]);

        $this->logger->info($method.' '.$url, $options);

        return $HttpClient->request($method, $url, $options);
    }
}
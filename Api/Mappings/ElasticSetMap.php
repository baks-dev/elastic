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

namespace BaksDev\Elastic\Api\Mappings;

use App\Kernel;
use BaksDev\Elastic\Api\ElasticClient;
use BaksDev\Elastic\Api\Properties\ElasticTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Table;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use Symfony\Component\HttpClient\Exception\TransportException;

final class ElasticSetMap extends ElasticClient
{
    private ?ArrayCollection $properties = null;

    private string $index;

    public function addProperty(ElasticTypeInterface $property): self
    {
        if(!$this->properties)
        {
            $this->properties = new ArrayCollection();
        }

        if(!$this->properties->contains($property))
        {
            $this->properties->add($property);
        }

        return $this;
    }

    public function handle(string $index): bool
    {
        if(Kernel::isTestEnvironment())
        {
            return true;
        }

        if($this->properties === null || $this->properties->isEmpty())
        {
            throw new InvalidArgumentException('');
        }

        if(class_exists($index))
        {
            $ref = new ReflectionClass($index);
            /** @var ReflectionAttribute $current */
            $current = current($ref->getAttributes(Table::class));
            $index = $current->getArguments()['name'] ?? $index;
        }

        $this->index = $index;

        $indexExists = $this->getIndex();

        $request = null;

        /** Если индекс уже существует - вычисляем расхождение, если находим новый индекс - добавляем */
        if($indexExists !== false)
        {
            /** @var ElasticTypeInterface $property */
            foreach($this->properties as $property)
            {
                if(
                    isset($indexExists[$property->getKey()]) &&
                    $indexExists[$property->getKey()]['type'] === $property->getType()
                )
                {
                    $this->properties->removeElement($property);
                }
            }

            if(!$this->properties->isEmpty())
            {
                /** Если имеется новый ключ - добавляем */
                $request = $this->request('PUT', '/'.$index.'/_mapping', [
                    'json' => $this->getProperty()['mappings']
                ]);
            }
        }
        else
        {
            $request = $this->request('PUT', '/'.$index, [
                'json' => $this->getProperty()
            ]);
        }

        if($request)
        {
            try
            {
                $response = $request->toArray(false);
            }
            catch(TransportException)
            {
                return false;
            }

            if($request->getStatusCode() !== 200)
            {
                $this->logger->critical(self::class.':'.__LINE__, $response);
                return false;
            }

            $this->logger->info(self::class.':'.__LINE__, $response);
        }

        return true;
    }


    private function getProperty(): array
    {
        $properties = [];

        /** @var ElasticTypeInterface $property */
        foreach($this->properties as $property)
        {
            $properties['mappings']['properties'][$property->getKey()]['type'] = $property->getType();

            if(!$property->isIndex())
            {
                $properties['mappings']['properties'][$property->getKey()]['index'] = false;
            }
        }

        return $properties;
    }


    private function getIndex(): bool|array
    {
        $request = $this->request('GET', '/'.$this->index.'/_mapping');

        try
        {
            $response = $request->toArray(false);
        }
        catch(TransportException $exception)
        {
            return false;
        }

        if($request->getStatusCode() !== 200)
        {
            return false;
        }


        return $response[$this->index]['mappings']['properties'];
    }

}

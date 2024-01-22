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

namespace BaksDev\Elastic\Api\Properties;

/** @see https://www.elastic.co/guide/en/elasticsearch/reference/current/date.html
 *
* JSON не имеет типа данных даты, поэтому даты в Elasticsearch могут быть следующими:
 *
 * строки, содержащие отформатированные даты, например. «2015-01-01» или «2015/01/01 12:10:30» или «2015-01-01T12:10:30Z».
 * число, представляющее миллисекунды с начала эпохи. «1420070400001»
 * число, представляющее секунды с начала эпохи (конфигурация). «asc»
 *
 * Внутренне даты преобразуются в формат UTC (если указан часовой пояс) и сохраняются в виде длинного числа, представляющего миллисекунды с начала эпохи.
 *
 */
final class DateElasticType implements ElasticTypeInterface
{
    private string $key;

    private bool $index = true;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public string $format = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis';

    /** Возвращает тип свойства */
    public function getType(): string
    {
        return 'date';
    }

    /** Возвращает ключ свойства */
    public function getKey(): string
    {
        return $this->key;
    }

    public function noIndex(): self
    {
        $this->index = false;
        return $this;
    }

    public function isIndex(): bool
    {
        return $this->index;
    }

    public function getFormat(): string
    {
        return 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis';
    }
}
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

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/keyword.html
 *
 * Семейство ключевых слов включает следующие типы полей:
 *
 * Ключевое слово, которое используется для структурированного контента, такого как идентификаторы, адреса электронной почты, имена хостов, коды состояния, почтовые индексы или теги.
 * Constant_keyword для полей ключевых слов, которые всегда содержат одно и то же значение.
 * подстановочный знак для неструктурированного контента, созданного машиной. Тип подстановочного знака оптимизирован для полей с большими значениями или высокой мощностью.
 *
 * Поля ключевых слов часто используются при сортировке, агрегировании и запросах на уровне терминов, таких как термин.
 *
 * Избегайте использования полей ключевых слов для полнотекстового поиска. Вместо этого используйте тип текстового поля.
 */
final class KeywordElasticType implements ElasticTypeInterface
{
    private string $key;

    private bool $index = true;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /** Возвращает тип свойства */
    public function getType(): string
    {
        return 'keyword';
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
}
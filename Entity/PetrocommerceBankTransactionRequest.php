<?php
/**
 * Copyright © 2015 Pavel A. Puchkov
 *
 * This file is part of the kino-khv.ru project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ItQuasar\PetrocommercePaymentGateBundle\Entity;
use ItQuasar\C4CoreBundle\Common\GetterSetter;

/**
 * Class PetrocommerceBankTransactionRequest
 * @package ItQuasar\PetrocommercePaymentGateBundle\Entity
 *
 * @method string getTimezoneOffset()
 * @method PetrocommerceBankTransactionRequest setTimezoneOffset(string $offset)
 *
 * @method string getMerchantName()
 * @method PetrocommerceBankTransactionRequest setMerchantName(string $name)
 *
 * @method string getMerchantUrl()
 * @method PetrocommerceBankTransactionRequest setMerchantUrl(string $url)
 *
 * @method string getDescription()
 * @method PetrocommerceBankTransactionRequest setDescription(string $description)
 *
 * @method string getBackUrl()
 * @method PetrocommerceBankTransactionRequest setBackUrl(string $backUrl)
 */
class PetrocommerceBankTransactionRequest extends PetrocommerceBankTransaction
{
    use GetterSetter;

    /**
     * @var string Смещение локального времени от Гринвича (знак нужен)
     */
    protected $timezoneOffset;

    /**
     * @var string Имя торговца. Используйте латиницу.
     */
    protected $merchantName;

    /**
     * @var string URL торговца.
     */
    protected $merchantUrl;

    /**
     * @var string Описание заказа в магазине.
     */
    protected $description;

    /**
     * @var string Адрес для возврата на Вашу страницу. Может быть для каждой операции разный или одинаковый.  В общем это Вам решать.
     * Сейчас этот адрес работает следующим образом: Вы указываете BACKREF в присылаемой форме и мы на итоговой странице,
     * показываемой клиенту, делаем редирект на указанный адрес, либо по нажатию кнопки, либо через js скрипт. Сейчас
     * сделана эмуляция нажатия кнопки посредством js. На Вашу страницу мы отправляем POST массив с полями по проведенной операции.
     */
    protected $backUrl;
}
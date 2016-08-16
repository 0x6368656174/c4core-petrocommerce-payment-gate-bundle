<?php
/**
 * Copyright © 2015 Pavel A. Puchkov
 *
 * This file is part of the kino-khv.ru project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ItQuasar\C4CorePetrocommercePaymentGateBundle\Entity;

use ItQuasar\C4CoreBundle\Common\GetterSetter;
use ItQuasar\C4CoreBundle\Entity\BankExchangeDocument;

/**
 * Class PetrocommerceBankTransaction
 *
 * @method float getAmount()
 * @method BankExchangeDocument setAmount(float $amount)
 *
 * @method string getCurrency()
 * @method BankExchangeDocument setCurrency(string $currency)
 *
 * @method int getBankOrderNumber()
 * @method BankExchangeDocument setBankOrderNumber(int $orderNumber)
 *
 * @method string getTerminalId()
 * @method PetrocommerceBankTransaction setTerminalId(string $id)
 *
 * @method int getTransactionType()
 * @method PetrocommerceBankTransaction setTransactionType(int $type)
 *
 * @method string getTimestamp()
 * @method PetrocommerceBankTransaction setTimestamp(string $timestamp)
 *
 * @method string getNonce()
 * @method PetrocommerceBankTransaction setNonce(string $nonce)
 *
 * @method string getMessageAuthenticationCode()
 * @method PetrocommerceBankTransaction setMessageAuthenticationCode(string $code)
 *
 * @method string getPSign()
 * @method PetrocommerceBankTransaction setPSign(string $pSign)
 *
 * @method string getMerchantId()
 * @method PetrocommerceBankTransactionRequest setMerchantId(string $id)
 * 
 * @method string getBankOrder()
 * @method PetrocommerceBankTransaction setBankOrder(string $order)
 */
abstract class PetrocommerceBankTransaction extends BankExchangeDocument
{
    use GetterSetter;

    const AuthorizeTransactionType = 0;
    const PayTransactionType = 21;
    const ReversalTransactionType = 24;
    const AuthorizeAndPayTransactionType = 1;

    /**
     * @var float Сумма
     */
    protected $amount;

    /**
     * @var string Валюта
     */
    protected $currency = 'RUR';

    /**
     * @var int Номер (идентификатор) заказа в системе магазина, уникален для каждого магазина в пределах системы
     */
    protected $bankOrderNumber;
    
    /**
     * @var string Номер терминала от имени которого идет запрос.
     */
    protected $terminalId;

    /**
     * @var int Тип операции (TRTYPE=0 - авторизация, TRTYPE=21 - финансовая транзакция), для жизни лучше использовать TRTYPE=1 –
     * это Retail financial request (при этом одновременно создаются авторизаци и финансовая транзакция). Чтобы сделать reversal
     * (отмену операции), нужно выполнить запрос с TRTYPE=24.
     */
    protected $transactionType;

    /**
     * @var string Время операции в формате YYYYMMDDHHMMSS. Тут Вы указываете текущее время по Гринвичу.
     */
    protected $timestamp;

    /**
     * @var string От 8 до 32 случайным образом сформированных байтов в hex формате. Используется для формирования электронной подписи сообщения.
     */
    protected $nonce;

    /**
     * @var string Код авторизации сообщения.
     */
    protected $messageAuthenticationCode;

    /**
     * @var string Электронная подпись.
     */
    protected $pSign;

    /**
     * @var string Номер торговца.
     */
    protected $merchantId;
    
    /**
     * @var string Заказ на нашей стороне, размерность по описанию не меньше 6 символов. Тут надо дать небольшое отступление.
     * Как только в системе рождается документ, все последующие будут считаться дубликатами, если совпадают terminal, order
     * и trtype. А документ рождается не только в случае успешной оплаты. Например, если у клиента недостаточно средств на
     * карте, оплата не пройдет, Вам вернется RC=51, но документ в системе родится. Если клиент позже пополнит счет и
     * попытается оплатить с тем же ордером, то вместо оплаты, он получит Duplicate transaction. Признак создания документа
     * в системе - RC > 0. Поэтому, если у вас будет необходимость давать клиенту проводить повторно операции, в случае
     * неудачи, то Вам надо отвязывать Ваш номер заказа от этого поля.
     */
    protected $bankOrder;
}
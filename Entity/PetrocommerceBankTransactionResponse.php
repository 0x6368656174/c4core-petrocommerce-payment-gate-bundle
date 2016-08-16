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

/**
 * Class PetrocommerceBankTransactionResponse
 *
 * @method int getAction()
 * @method PetrocommerceBankTransactionResponse setAction(int $action)
 *
 * @method string getResponseCode()
 * @method PetrocommerceBankTransactionResponse setResponseCode(string $code)
 *
 * @method string getApprovalCode()
 * @method PetrocommerceBankTransactionResponse setApprovalCode(string $code)
 *
 * @method string getRetrievalReferenceNumber()
 * @method PetrocommerceBankTransactionResponse setRetrievalReferenceNumber(string $number)
 *
 * @method string getInternalReferenceNumber()
 * @method PetrocommerceBankTransactionResponse setInternalReferenceNumber(string $number)
 */
class PetrocommerceBankTransactionResponse extends PetrocommerceBankTransaction
{
    use GetterSetter;

    /**
     * @var int Действие эквайринга. Может быть:
     * * 0 - транзакция выполнена успешно;
     * * 1 - найден дубликат транзакции;
     * * 2 - в транзакции отказано;
     * * 3 - ошибка проведения транзакции;
     * * 4 - информационное сообщение.
     */
    protected $action;

    /**
     * @var string Код ответа транзакции.
     */
    protected $responseCode;

    /**
     * @var string Код одобрения транзакции.
     */
    protected $approvalCode;

    /**
     * @var string Код для поиска транзакции. Используется для отмены транзакции.
     */
    protected $retrievalReferenceNumber;

    /**
     * @var string Внешний код транзакции.
     */
    protected $internalReferenceNumber;
}
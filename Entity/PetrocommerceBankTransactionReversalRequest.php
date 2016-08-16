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
 * Class PetrocommerceBankTransactionReversalRequest
 *
 * @method string getRetrievalReferenceNumber()
 * @method PetrocommerceBankTransactionResponse setRetrievalReferenceNumber(string $number)
 *
 * @method string getInternalReferenceNumber()
 * @method PetrocommerceBankTransactionResponse setInternalReferenceNumber(string $number)
 */
class PetrocommerceBankTransactionReversalRequest extends PetrocommerceBankTransactionRequest
{
    use GetterSetter;

    /**
     * @var string Код для поиска транзакции. Используется для отмены транзакции.
     */
    protected $retrievalReferenceNumber;

    /**
     * @var string Внешний код транзакции.
     */
    protected $internalReferenceNumber;
}
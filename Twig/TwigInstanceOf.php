<?php
/**
 * Copyright © 2015 Pavel A. Puchkov
 *
 * This file is part of the kino-khv.ru project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ItQuasar\PetrocommercePaymentGateBundle\Twig;

use ItQuasar\C4CorePetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionRequest;
use ItQuasar\C4CorePetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionResponse;
use ItQuasar\C4CorePetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionReversalRequest;

class TwigInstanceOf extends \Twig_Extension
{
    public function getName()
    {
        return 'c4_core_petrocommerce_payment_gate_instance_of';
    }

    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('PetrocommerceBankTransactionRequest', function ($event) { return $event instanceof PetrocommerceBankTransactionRequest; }),
            new \Twig_SimpleTest('PetrocommerceBankTransactionReversalRequest', function ($event) { return $event instanceof PetrocommerceBankTransactionReversalRequest; }),
            new \Twig_SimpleTest('PetrocommerceBankTransactionResponse', function ($event) { return $event instanceof PetrocommerceBankTransactionResponse; })
        );
    }
}
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

use ItQuasar\PetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionRequest;
use ItQuasar\PetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionResponse;
use ItQuasar\PetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionReversalRequest;

class TwigInstanceOf extends \Twig_Extension
{
    public function getName()
    {
        return 'petrocommerce_payment_gate_instance_of';
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
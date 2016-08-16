<?php

namespace ItQuasar\C4CorePetrocommercePaymentGateBundle;

use ItQuasar\C4CorePetrocommercePaymentGateBundle\DependencyInjection\ItQuasarPetrocommercePaymentGateExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ItQuasarC4CorePetrocommercePaymentGateBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new ItQuasarPetrocommercePaymentGateExtension();
        }

        return $this->extension;
    }
}

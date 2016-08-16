<?php

namespace ItQuasar\PetrocommercePaymentGateBundle;

use ItQuasar\PetrocommercePaymentGateBundle\DependencyInjection\ItQuasarPetrocommercePaymentGateExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ItQuasarPetrocommercePaymentGateBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new ItQuasarPetrocommercePaymentGateExtension();
        }

        return $this->extension;
    }
}

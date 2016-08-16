<?php
/**
 * Copyright © 2015 Pavel A. Puchkov
 *
 * This file is part of the kino-khv.ru project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ItQuasar\C4CorePetrocommercePaymentGateBundle\Tests\Services;

use ItQuasar\C4CorePetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionResponse;
use ItQuasar\C4CorePetrocommercePaymentGateBundle\Services\GateService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BankTransactionProcessorServiceTest extends KernelTestCase
{
    /** @var  GateService */
    private $service;

    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('iq_petrocommerce_payment_gate.gate');
    }

    private function createTestResponse()
    {
        $response = new PetrocommerceBankTransactionResponse();
        $response->setAction(0);
        $response->setResponseCode('00');
        $response->setApprovalCode('187836');
        $response->setCurrency('RUR');
        $response->setAmount(250.00);
        $response->setTerminalId('9999EC65');
        $response->setTransactionType(1);
        $response->setBankOrder('000090');
        $response->setRetrievalReferenceNumber('530901562562');
        $response->setMerchantId('9999EC650000000');
        $response->setTimestamp('20151105080145');
        $response->setInternalReferenceNumber('1B81DA96D55AA508');
        $response->setNonce('3057536355614A636442674939746D424E3969674F6C6461633574677A73697A');
        $response->setPSign('69D7FE9004E5910687FA89DAF40E47B3063FCC24D96A98D95B4D9EE125811FAC0E757F46190CFB44112C56D123AEA02AEA347F3D2718AB32D6F2A325465BCA6C0953CA5DC7E1A07F32015AE5098C9C47A4DD84AE484D7CCA70B5C1A5910783DF7E6B7434A1B7BD4B048582F0E25187D080A7F22A962A66605E3B68A51430FE3F');

        return $response;
    }

    public function testResponseMac() {
        $response = $this->createTestResponse();

        $this->service->verifyResponse($response);

        $validMac = '1020061878363RUR6250.0089999EC6511600009012530901562562159999EC6500000001420151105080145161B81DA96D55AA508643057536355614A636442674939746D424E3969674F6C6461633574677A73697A';

        $this->assertEquals($validMac, $response->getMessageAuthenticationCode());
    }

    public function testResponseSign() {
        $response = $this->createTestResponse();

        $sign = $this->service->verifyResponse($response);

        $this->assertTrue($sign);
    }
}
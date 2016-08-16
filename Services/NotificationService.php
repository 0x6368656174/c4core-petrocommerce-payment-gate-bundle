<?php
/**
 * Copyright © 2015 Pavel A. Puchkov
 *
 * This file is part of the kino-khv.ru project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ItQuasar\C4CorePetrocommercePaymentGateBundle\Services;

use ItQuasar\C4CoreBundle\Services\Curl;
use ItQuasar\C4CoreBundle\Services\FilePaths;
use ItQuasar\C4CoreBundle\AbstractService\Notification;
use ItQuasar\C4CoreBundle\Entity\Order;
use ItQuasar\C4CoreBundle\Exception\ConnectToNotifyServiceException;
use ItQuasar\C4CoreBundle\Exception\NotFoundBankPayRequestException;
use ItQuasar\C4CoreBundle\Exception\ParseNotifyServiceResponseException;
use ItQuasar\C4CorePetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionRequest;
use ItQuasar\C4CorePetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionResponse;
use Symfony\Component\Translation\Translator;

class NotificationService extends Notification
{
    protected $curl;
    protected $curlService;
    protected $notifyUrl;
    protected $notifyUrlDev;
    protected $translator;
    protected $filePaths;
    protected $environment;

    public function __construct(Curl $curlService, Translator $translator, FilePaths $filePaths)
    {
        $this->curlService = $curlService;
        $this->translator = $translator;
        $this->filePaths = $filePaths;

        $this->curl = curl_init();
        $this->curlService->init($this->curl);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    public function findPayResponses(Order $order)
    {
        $bankRequest = null;

        foreach ($order->getBankTransactions() as $bankTransaction) {
            if ($bankTransaction instanceof PetrocommerceBankTransactionRequest) {
                $bankRequest = $bankTransaction;
                break;
            }
        }

        if (!$bankRequest)
            throw new NotFoundBankPayRequestException($this->translator);

        $baseUrl = $this->notifyUrl;
        if ($this->environment == 'dev')
            $baseUrl = $this->notifyUrlDev;

        $url = $baseUrl . '?' . 'Function=getnotif'
            . '&TRTYPE='. $bankRequest->getTransactionType()
            . '&ORDER=' . $bankRequest->getBankOrder()
            . '&MERCHANT=' . $bankRequest->getMerchantId();

        $response = $this->curlService->exec($this->curl, $url);

        if ($response === false)
            throw new ConnectToNotifyServiceException($this->translator, curl_error($this->curl));

        //fixme: Костыль, обходящий не валидный XML-ответ тупиц из банка Петрокомерц
        $response = str_replace('&', '&amp;', $response);

        try {
            $xml = new \SimpleXMLElement($response);
        } catch (\Exception $e) {
            throw new ParseNotifyServiceResponseException($this->translator, $response);
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $notif = $xml->notif;

        if (!$notif)
            return null;

        $bankResponses = array();
        foreach ($notif as $notifRow) {
            $params = array();
            parse_str($notifRow, $params);

            $bankResponse = new PetrocommerceBankTransactionResponse();

            $bankResponse->setAction((int)$params['ACTION']);
            $bankResponse->setResponseCode($params['RC']);
            $bankResponse->setApprovalCode($params['APPROVAL']);
            $bankResponse->setCurrency($params['CURRENCY']);
            $bankResponse->setAmount($params['AMOUNT']);
            $bankResponse->setTerminalId($params['TERMINAL']);
            $bankResponse->setTransactionType($params['TRTYPE']);
            $bankResponse->setBankOrder($params['ORDER']);
            $bankResponse->setBankOrderNumber((int)$params['ORDER']);
            $bankResponse->setRetrievalReferenceNumber($params['RRN']);
            $bankResponse->setMerchantId($params['MERCHANT']);
            $bankResponse->setTimestamp($params['TIMESTAMP']);
            $bankResponse->setInternalReferenceNumber($params['INT_REF']);
            $bankResponse->setNonce($params['NONCE']);
            $bankResponse->setPSign($params['P_SIGN']);

            $bankResponses []= $bankResponse;
        }

        return $bankResponses;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    public function setNotifyUrlDev($notifyUrlDev)
    {
        $this->notifyUrlDev = $notifyUrlDev;
    }

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    public function setClientCertFile($file)
    {
        if ($this->environment != 'dev')
            $this->curlService->setClientCertFile($this->curl, $this->filePaths->getRootDir(). '/' . $file);
    }

    public function setClientKeyFile($file)
    {
        if ($this->environment != 'dev')
            $this->curlService->setClientKeyFile($this->curl, $this->filePaths->getRootDir(). '/' . $file);
    }

    public function setCaCertFile($file)
    {
        if ($this->environment != 'dev')
            $this->curlService->setCaCertFile($this->curl, $this->filePaths->getRootDir(). '/' . $file);
    }

    public function setClientCertFileDev($file)
    {
        if ($this->environment == 'dev')
            $this->curlService->setClientCertFile($this->curl, $this->filePaths->getRootDir(). '/' . $file);
    }

    public function setClientKeyFileDev($file)
    {
        if ($this->environment == 'dev')
            $this->curlService->setClientKeyFile($this->curl, $this->filePaths->getRootDir(). '/' . $file);
    }

    public function setCaCertFileDev($file)
    {
        if ($this->environment == 'dev')
            $this->curlService->setCaCertFile($this->curl, $this->filePaths->getRootDir(). '/' . $file);
    }
}
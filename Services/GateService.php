<?php
/**
 * Copyright © 2015 Pavel A. Puchkov
 *
 * This file is part of the kino-khv.ru project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ItQuasar\PetrocommercePaymentGateBundle\Services;

use Doctrine\ORM\EntityManager;
use ItQuasar\C4CoreBundle\Services\FilePaths;
use ItQuasar\C4CoreBundle\AbstractService\Gate;
use ItQuasar\C4CoreBundle\Entity\Order;
use ItQuasar\C4CoreBundle\Entity\BankExchangeDocument;
use ItQuasar\C4CoreBundle\Entity\HttpPayRequest;
use ItQuasar\C4CoreBundle\Exception\BadActionException;
use ItQuasar\C4CoreBundle\Exception\NotFoundBankOrderException;
use ItQuasar\C4CoreBundle\Exception\BankResponseAlreadyProcessed;
use ItQuasar\C4CoreBundle\Exception\NegativeActionException;
use ItQuasar\C4CoreBundle\Exception\RequestAlreadyExistException;
use ItQuasar\PetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionRequest;
use ItQuasar\PetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionResponse;
use ItQuasar\PetrocommercePaymentGateBundle\Entity\PetrocommerceBankTransactionReversalRequest;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

class GateService extends Gate
{
    const AuthorizeTransactionType = 0;
    const PayTransactionType = 21;
    const ReversalTransactionType = 24;
    const AuthorizeAndPayTransactionType = 1;
    
    protected $orderEntity;

    protected $em;
    protected $translator;
    protected $filePaths;
    protected $router;
    protected $environment;

    protected $currency;
    protected $merchantName;
    protected $merchantId;
    protected $singPrivateKeyFile;
    protected $singBankPublicKeyFile;
    protected $iaUrl;

    protected $terminalIdDev;
    protected $merchantIdDev;
    protected $singPrivateKeyFileDev;
    protected $singBankPublicKeyFileDev;
    protected $iaUrlDev;

    public function __construct(EntityManager $em, Translator $translator, FilePaths $filePaths, Router $router)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->filePaths = $filePaths;
        $this->router = $router;
    }

    public function verifyResponse(BankExchangeDocument &$response)
    {
        /** @var PetrocommerceBankTransactionResponse $response */
        
        if (!is_null($response->getAction()))
            $action = strlen($response->getAction()).$response->getAction();
        else
            $action = '-';

        if (!empty($response->getResponseCode()))
            $responseCode = strlen($response->getResponseCode()).$response->getResponseCode();
        else
            $responseCode = '-';

        if (!empty($response->getApprovalCode()))
            $approvalCode = strlen($response->getApprovalCode()).$response->getApprovalCode();
        else
            $approvalCode = '-';

        if (!empty($response->getCurrency()))
            $currency = strlen($response->getCurrency()).$response->getCurrency();
        else
            $currency = '-';

        if (!empty($response->getAmount())){
            $amount = number_format($response->getAmount(), 2, '.', '');
            $amount = strlen($amount).$amount;
        } else
            $amount = '-';

        if (!empty($response->getTerminalId()))
            $terminalId = strlen($response->getTerminalId()).$response->getTerminalId();
        else
            $terminalId = '-';

        if (!empty($response->getTransactionType()))
            $transactionType = strlen($response->getTransactionType()).$response->getTransactionType();
        else
            $transactionType = '-';

        if (!empty($response->getBankOrder()))
            $bankOrder = strlen($response->getBankOrder()).$response->getBankOrder();
        else
            $bankOrder = '-';

        if (!empty($response->getRetrievalReferenceNumber()))
            $rrn = strlen($response->getRetrievalReferenceNumber()).$response->getRetrievalReferenceNumber();
        else
            $rrn = '-';

        if (!empty($response->getMerchantId()))
            $merchantId = strlen($response->getMerchantId()).$response->getMerchantId();
        else
            $merchantId = '-';

        if (!empty($response->getTimestamp()))
            $timestamp = strlen($response->getTimestamp()).$response->getTimestamp();
        else
            $timestamp = '-';

        if (!empty($response->getInternalReferenceNumber()))
            $int_ref = strlen($response->getInternalReferenceNumber()).$response->getInternalReferenceNumber();
        else
            $int_ref = '-';

        if (!empty($response->getNonce())){
            $nonce = trim($response->getNonce());
            $nonce = strlen($nonce).$nonce;
        }
        else
            $nonce = '-';

        $mac = $action
            .$responseCode
            .$approvalCode
            .$currency
            .$amount
            .$terminalId
            .$transactionType
            .$bankOrder
            .$rrn
            .$merchantId
            .$timestamp
            .$int_ref
            .$nonce;

        $response->setMessageAuthenticationCode($mac);

        if ($this->environment != 'dev'
            && $this->environment != 'test')
            $publicKeyFilePath = 'file://'. $this->filePaths->getRootDir(). '/' . $this->singBankPublicKeyFile;
        else
            $publicKeyFilePath = 'file://'. $this->filePaths->getRootDir(). '/' . $this->singBankPublicKeyFileDev;

        $publicKey = openssl_pkey_get_public($publicKeyFilePath);
        $result = openssl_verify($mac, pack('H*', $response->getPSign()), $publicKey, OPENSSL_ALGO_MD5) === 1;

        openssl_free_key($publicKey);

        return $result;
    }

    public function signRequest(BankExchangeDocument &$request)
    {
        /** @var PetrocommerceBankTransactionRequest $request */
        
        if (!empty($request->getCurrency()))
            $currency = strlen($request->getCurrency()).$request->getCurrency();
        else
            $currency = '-';

        if (!empty($request->getAmount())){
            $amount = number_format($request->getAmount(), 2, '.', '');
            $amount = strlen($amount).$amount;
        } else
            $amount = '-';

        if (!empty($request->getTerminalId()))
            $terminalId = strlen($request->getTerminalId()).$request->getTerminalId();
        else
            $terminalId = '-';

        if (!empty($request->getTransactionType()))
            $transactionType = strlen($request->getTransactionType()).$request->getTransactionType();
        else
            $transactionType = '-';

        if (!empty($request->getBankOrder()))
            $bankOrder = strlen($request->getBankOrder()).$request->getBankOrder();
        else
            $bankOrder = '-';

        if (!empty($request->getMerchantId()))
            $merchantId = strlen($request->getMerchantId()).$request->getMerchantId();
        else
            $merchantId = '-';

        if (!empty($request->getTimezoneOffset()))
            $timezoneOffset = strlen($request->getTimezoneOffset()).$request->getTimezoneOffset();
        else
            $timezoneOffset = '-';

        if (!empty($request->getTimestamp()))
            $timestamp = strlen($request->getTimestamp()).$request->getTimestamp();
        else
            $timestamp = '-';

        if (!empty($request->getNonce())){
            $nonce = trim($request->getNonce());
            $nonce = strlen($nonce).$nonce;
        }
        else
            $nonce = '-';

        $mac = $amount
            .$currency
            .$bankOrder
            .$merchantId
            .$terminalId
            .$transactionType
            .$timezoneOffset
            .$timestamp
            .$nonce;

        $request->setMessageAuthenticationCode($mac);

        if ($this->environment != 'dev')
            $privateKeyFilePath = 'file://'. $this->filePaths->getRootDir(). '/' . $this->singPrivateKeyFile;
        else
            $privateKeyFilePath = 'file://'. $this->filePaths->getRootDir(). '/' . $this->singPrivateKeyFileDev;

        $privateKey = openssl_pkey_get_private($privateKeyFilePath);
        $pSign = '';
        openssl_sign($mac, $pSign, $privateKey, OPENSSL_ALGO_MD5);
        openssl_free_key($privateKey);
        $pSign = strtoupper(bin2hex($pSign));

        $request->setPSign($pSign);
    }

    public function processPayResponse(BankExchangeDocument &$response)
    {
        /** @var PetrocommerceBankTransactionResponse $response */
        
//        if (!$this->verifyResponse($response))
//            throw new InvalidPSignException($this->translator);
        $this->verifyResponse($response);

        $query = $this->em->createQueryBuilder()
            ->select('t')
            ->from('ItQuasarPetrocommercePaymentGateBundle:PetrocommerceBankTransactionResponse', 't')
            ->andWhere('t.bankOrderNumber = :orderNumber')
            ->andWhere('t.transactionType = :transactionType')
            ->andWhere('t.timestamp = :timestamp')
            ->getQuery();

        $query->setParameter(':orderNumber', $response->getBankOrderNumber());
        $query->setParameter(':transactionType', $response->getTransactionType());
        $query->setParameter(':timestamp', $response->getTimestamp());

        /** @var PetrocommerceBankTransactionResponse $oldResponse */
        $oldResponse = $query->getOneOrNullResult();
        if ($oldResponse) {
            $e = new BankResponseAlreadyProcessed($this->translator, $oldResponse);
            throw $e;
        }
        
        $query = $this->em->createQueryBuilder()
            ->select('r')
            ->from('ItQuasarPetrocommercePaymentGateBundle:PetrocommerceBankTransactionRequest', 'r')
            ->where('r.bankOrderNumber = :orderNumber')
            ->getQuery();
        
        $query->setParameter(':orderNumber', $response->getBankOrderNumber());
        
        /** @var PetrocommerceBankTransactionRequest $request */
        $request = $query->getOneOrNullResult();
        if (!$request)
            throw new NotFoundBankOrderException($this->translator, $response->getBankOrderNumber());

        $query = $this->em->createQueryBuilder()
            ->select('o')
            ->from($this->orderEntity, 'o')
            ->where('o = :order')
            ->getQuery();

        $query->setParameter(':order', $request->getOrder());

        /** @var Order $order */
        $order = $query->getOneOrNullResult();

        if (!$order)
            throw new NotFoundBankOrderException($this->translator, $response->getBankOrderNumber());

        $response->setOrder($order);
        $this->em->persist($response);

        if ($response->getTransactionType() == self::AuthorizeAndPayTransactionType)
            $order->setPaymentResponseDateTime(new \DateTime());

        if ($response->getAction() != '0') {
            if ((int)$response->getResponseCode() < 0) {
                $e = new NegativeActionException($this->translator, $response);
                throw $e;
            } else if ($response->getResponseCode() != '00') {
                $order->setBankStatus(Order::Rejected);
                $this->em->flush();

                $e = new BadActionException($this->translator, $response);
                throw $e;
            }
        }

        if ($response->getTransactionType() == self::AuthorizeAndPayTransactionType)
            $order->setBankStatus(Order::Payed);
        else
            $order->setBankStatus(Order::Reversal);

        $this->em->flush();
    }

    public function createPayReversalRequest(Order $order, BankExchangeDocument $response)
    {
        /** @var PetrocommerceBankTransactionResponse $response */
        
        //Попробуем найти старый запрос
        /** @var PetrocommerceBankTransactionRequest $oldRequest */
        $oldRequest = $this->em->getRepository('ItQuasarKinoKhvBundle:PetrocommerceBankTransactionReversalRequest')->findOneBy(array(
            'bankOrderNumber' => $response->getBankOrderNumber()
        ));

        if ($oldRequest)
            throw new RequestAlreadyExistException($this->translator, $oldRequest);

        $bankOrder = $response->getBankOrder();
        $amount = $response->getAmount();

        $timestamp = gmdate('YmdHis');

        $dateTimeZone = new \DateTimeZone("Asia/Vladivostok");
        $dateTime = new \DateTime();
        $timezoneOffset = $dateTimeZone->getOffset($dateTime) / 3600;
        if ($timezoneOffset > 0)
            $timezoneOffset = '+' . $timezoneOffset;

        $nonce = strtoupper(bin2hex($this->generateRandomString(32)));

        $merchantId = $response->getMerchantId();

        $terminalId = $response->getTerminalId();

        $reversalBackUrl = $this->router->generate('reversalResultProcess', array(), Router::ABSOLUTE_URL);

        $merchantUrl = $this->router->generate('index', array(), Router::ABSOLUTE_URL);

        $request = new PetrocommerceBankTransactionReversalRequest();
        $request->setOrder($order);
        $request->setAmount($amount);
        $request->setCurrency($this->currency);
        $request->setBankOrderNumber($response->getBankOrderNumber());
        $request->setBankOrder($bankOrder);
        $request->setDescription($this->translator->trans('message.bank_transaction_reversal'));
        $request->setTerminalId($terminalId);
        $request->setTransactionType(self::ReversalTransactionType);
        $request->setMerchantName($this->merchantName);
        $request->setMerchantUrl($merchantUrl);
        $request->setMerchantId($merchantId);
        $request->setBackUrl($reversalBackUrl);
        $request->setTimestamp($timestamp);
        $request->setTimezoneOffset($timezoneOffset);
        $request->setNonce($nonce);
        $request->setRetrievalReferenceNumber($response->getRetrievalReferenceNumber());
        $request->setInternalReferenceNumber($response->getInternalReferenceNumber());

        $this->signRequest($request);

        $this->em->persist($request);

        $order->setBankStatus(Order::Waiting);
        $this->em->flush();

        return $request;
    }

    public function createPayRequest(Order $order, array $extraParameters = array())
    {
        //Если запрос уже существует
        $query = $this->em->createQueryBuilder()
            ->select('r')
            ->from('ItQuasarPetrocommercePaymentGateBundle:PetrocommerceBankTransactionRequest', 'r')
            ->where('r.order = :order')
            ->getQuery();
        $query->setParameter(':order', $order);

        $oldRequest = $query->getOneOrNullResult();

        if ($oldRequest)
            return $oldRequest;

        $timestamp = gmdate('YmdHis');

        $dateTimeZone = new \DateTimeZone("Asia/Vladivostok");
        $dateTime = new \DateTime();
        $timezoneOffset = $dateTimeZone->getOffset($dateTime) / 3600;
        if ($timezoneOffset > 0)
            $timezoneOffset = '+' . $timezoneOffset;

        $nonce = strtoupper(bin2hex($this->generateRandomString(32)));

        if ($this->environment != 'dev')
            $merchantId = $this->merchantId;
        else
            $merchantId = $this->merchantIdDev;

        $terminalId = $extraParameters['terminalId'];

        $backUrl = $this->router->generate('payResultProcess', array(), Router::ABSOLUTE_URL);

        $merchantUrl = $this->router->generate('index', array(), Router::ABSOLUTE_URL);

        $request = new PetrocommerceBankTransactionRequest();
        $request->setOrder($order);
        $request->setAmount($order->getAmount());
        $request->setCurrency($this->currency);
        $request->setDescription($order->getDescription());
        $request->setTerminalId($terminalId);
        $request->setTransactionType(self::AuthorizeAndPayTransactionType);
        $request->setMerchantName($this->merchantName);
        $request->setMerchantUrl($merchantUrl);
        $request->setMerchantId($merchantId);
        $request->setBackUrl($backUrl);
        $request->setTimestamp($timestamp);
        $request->setTimezoneOffset($timezoneOffset);
        $request->setNonce($nonce);

        $this->em->persist($request);
        $this->em->flush();

        $bankOrderNumber = $request->getId();
        $bankOrder = sprintf('%06d', $bankOrderNumber);

        $request->setBankOrderNumber($bankOrderNumber);
        $request->setBankOrder($bankOrder);

        $this->signRequest($request);

        $order->setBankStatus(Order::Waiting);
        $this->em->flush();

        return $request;
    }

    public function processHttpPayResponse(Request $httpRequest)
    {
        $bankResponse = new PetrocommerceBankTransactionResponse();
        
        $bankResponse->setAction((int)$httpRequest->get('ACTION'));
        $bankResponse->setResponseCode($httpRequest->get('RC'));
        $bankResponse->setApprovalCode($httpRequest->get('APPROVAL'));
        $bankResponse->setCurrency($httpRequest->get('CURRENCY'));
        $bankResponse->setAmount($httpRequest->get('AMOUNT'));
        $bankResponse->setTerminalId($httpRequest->get('TERMINAL'));
        $bankResponse->setTransactionType($httpRequest->get('TRTYPE'));
        $bankResponse->setBankOrder($httpRequest->get('ORDER'));
        $bankResponse->setBankOrderNumber((int)$httpRequest->get('ORDER'));
        $bankResponse->setRetrievalReferenceNumber($httpRequest->get('RRN'));
        $bankResponse->setMerchantId($httpRequest->get('MERCHANT'));
        $bankResponse->setTimestamp($httpRequest->get('TIMESTAMP'));
        $bankResponse->setInternalReferenceNumber($httpRequest->get('INT_REF'));
        $bankResponse->setNonce($httpRequest->get('NONCE'));
        $bankResponse->setPSign($httpRequest->get('P_SIGN'));
        
        return $bankResponse;
    }

    public function createHttpPayRequest(BankExchangeDocument $bankRequest)
    {
        /** @var PetrocommerceBankTransactionRequest $bankRequest */
        $postData = array (
            'confirmorder'=> 'Y',
            'TRTYPE' => $bankRequest->getTransactionType(),
            'MERCHANT' => $bankRequest->getMerchantId(),
            'TERMINAL' => $bankRequest->getTerminalId(),
            'CURRENCY' => $bankRequest->getCurrency(),
            'ORDER' => $bankRequest->getBankOrder(),
            'AMOUNT' => number_format($bankRequest->getAmount(), 2, '.', ''),
            'DESC' => $bankRequest->getDescription(),
            'BACKREF' => $bankRequest->getBackUrl(),
            'MERCH_NAME' => $bankRequest->getMerchantName(),
            'MERCH_URL' => $bankRequest->getMerchantUrl(),
            'MERCH_GMT' => $bankRequest->getTimezoneOffset(),
            'TIMESTAMP' => $bankRequest->getTimestamp(),
            'NONCE' => $bankRequest->getNonce(),
            'P_SIGN' => $bankRequest->getPSign()
        );
        
        $httpRequest = new HttpPayRequest();
        $httpRequest->setPostData($postData);

        if ($this->environment != 'dev')
            $httpRequest->setUrl($this->iaUrl);
        else
            $httpRequest->setUrl($this->iaUrlDev);
        
        return $httpRequest;
    }

    private static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param mixed $merchantName
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
    }

    /**
     * @param mixed $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param mixed $singPrivateKeyFile
     */
    public function setSingPrivateKeyFile($singPrivateKeyFile)
    {
        $this->singPrivateKeyFile = $singPrivateKeyFile;
    }

    /**
     * @param mixed $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param mixed $singPrivateKeyFileDev
     */
    public function setSingPrivateKeyFileDev($singPrivateKeyFileDev)
    {
        $this->singPrivateKeyFileDev = $singPrivateKeyFileDev;
    }

    /**
     * @param mixed $merchantIdDev
     */
    public function setMerchantIdDev($merchantIdDev)
    {
        $this->merchantIdDev = $merchantIdDev;
    }

    /**
     * @return mixed
     */
    public function getTerminalIdDev()
    {
        return $this->terminalIdDev;
    }

    /**
     * @param mixed $terminalIdDev
     */
    public function setTerminalIdDev($terminalIdDev)
    {
        $this->terminalIdDev = $terminalIdDev;
    }

    /**
     * @param mixed $singBankPublicKeyFile
     */
    public function setSingBankPublicKeyFile($singBankPublicKeyFile)
    {
        $this->singBankPublicKeyFile = $singBankPublicKeyFile;
    }

    /**
     * @param mixed $singBankPublicKeyFileDev
     */
    public function setSingBankPublicKeyFileDev($singBankPublicKeyFileDev)
    {
        $this->singBankPublicKeyFileDev = $singBankPublicKeyFileDev;
    }

    /**
     * @return mixed
     */
    public function getOrderEntity()
    {
        return $this->orderEntity;
    }

    /**
     * @param mixed $orderEntity
     */
    public function setOrderEntity($orderEntity)
    {
        $this->orderEntity = $orderEntity;
    }

    /**
     * @param mixed $iaUrl
     */
    public function setIaUrl($iaUrl)
    {
        $this->iaUrl = $iaUrl;
    }

    /**
     * @param mixed $iaUrlDev
     */
    public function setIaUrlDev($iaUrlDev)
    {
        $this->iaUrlDev = $iaUrlDev;
    }
}
<?php

namespace TrustPay\CardOnFile;

use TrustPay\Enums\CardTransactionType;
use TrustPay\HttpClient\Client;
use TrustPay\RequestAwareTrait;
use TrustPay\RequestInterface;
use TrustPay\Response;
use TrustPay\SignatureValidator;

class Request implements RequestInterface
{
    use RequestAwareTrait;

    /** @var Client */
    private $httpClient;

    /** @var string */
    private $storedCardToken;

    /**
     * Request constructor.
     *
     * @param $accountId
     * @param $secret
     * @param $endpoint
     */
    public function __construct($accountId, $secret, $endpoint)
    {
        $this->setAccountId($accountId);
        $this->setSignatureValidator(new SignatureValidator($secret));
        $this->setEndpoint($endpoint);
        $this->httpClient = new Client($endpoint);
    }

    /**
     * @param $storedCardToken
     *
     * @return string
     */
    public function payment($storedCardToken)
    {
        $this->storedCardToken = $storedCardToken;

        $requestUrl = $this->getUrl();
	    return $requestUrl;
//        $response = $this->httpClient->get($requestUrl);
//
//        $response = $this->parseBackgroundResponse($response);
//        $response->setRequestedUrl($requestUrl);
//
//        return $response;
    }

    /**
     * @return string
     */
    protected function buildQuery()
    {
        $queryData = $this->getDefaultQueryData();

        $queryData = array_merge($queryData, $this->getCardOnFileQueryData());

        return http_build_query($queryData);
    }

    /**
     * @param $result
     *
     * @return Response
     */
    private function getResponse($result)
    {

    }

    /**
     * @return array
     */
    private function getCardOnFileQueryData()
    {
        $card = (new Serializer())->deserialize($this->storedCardToken);

        $message = $this->signatureValidator->createMessage(
            $this->accountId,
            $this->amount,
            $this->currency,
            $this->reference,
            CardTransactionType::CARD_ON_FILE,
            $card['CardID']
        );

        $queryData = [
            'CTY'     => CardTransactionType::CARD_ON_FILE,
            'CardID'  => $card['CardID'],
            'SIG2'    => $this->signatureValidator->computeSignature($message),
        ];

        $queryData = array_filter($queryData, function ($value) {
            return $value !== null;
        });

        return $queryData;
    }
}
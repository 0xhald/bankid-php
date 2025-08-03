<?php

namespace Dimafe6\BankID\Service;

use Dimafe6\BankID\Model\CollectResponse;
use Dimafe6\BankID\Model\OrderResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class BankIDService
 *
 * @category PHP
 * @package  Dimafe6\BankID\Service
 * @author   Dmytro Feshchenko <dimafe2000@gmail.com>
 */
class BankIDService
{
    /** @var Client $client Guzzle http client */
    private $client;

    /** @var string $apiUrl BankID API base url */
    private $apiUrl;

    /** @var array $options Guzzle client options. @see http://docs.guzzlephp.org/en/stable/request-options.html */
    private $options;

    /** @var string $endUserIp The user IP address as seen by RP. String. IPv4 and IPv6 is allowed */
    private $endUserIp;

    /**
     * BankIDService constructor.
     * @param string $apiUrl
     * @param string $endUserIp
     * @param array $options
     */
    public function __construct($apiUrl, $endUserIp, $options = [])
    {
        $this->apiUrl    = $apiUrl;
        $this->endUserIp = $endUserIp;

        $options['base_uri'] = $apiUrl;
        $options['json']     = true;

        $this->options = $options;

        $this->client = new Client($this->options);
    }

    /**
     * @return OrderResponse
     * @throws ClientException
     */
    public function getAuthResponse()
    {
        $parameters = [
            'endUserIp'      => $this->endUserIp
        ];

        $responseData = $this->client->post('auth', ['json' => $parameters]);

        $response = new OrderResponse($responseData);

        return $response;
    }


    /**
     * @param string|null $personalNumber The personal number of the user. String. 12 digits. Century must be included.
     * @param string $userVisibleData Text displayed to the user during the order. The purpose is to provide context, thereby enabling the user to detect identification errors and avert fraud attempts. The text can be formatted using CR, LF and CRLF for new lines. The text must be encoded as UTF-8 and then base 64 encoded.
     * @param string $userVisibleDataFormat Format for data displayed to the user
     * @return OrderResponse
     * @throws ClientException
     */
    public function getSignResponse($userVisibleData = null, $userVisibleDataFormat = null)
    {
        if (is_null($userVisibleData)) {
            throw new \InvalidArgumentException('userVisibleData field is required');
        }

        $parameters = [
            'endUserIp'       => $this->endUserIp,
            'userVisibleData' => base64_encode($userVisibleData),
        ];

        if (!is_null($userVisibleDataFormat)) {
            $parameters['userVisibleDataFormat'] = $userVisibleDataFormat;
        }

        $responseData = $this->client->post('sign', ['json' => $parameters]);

        return new OrderResponse($responseData);
    }

    /**
     * @param string $orderRef Used to collect the status of the order.
     * @return CollectResponse
     * @throws ClientException
     */
    public function collectResponse($orderRef)
    {
        $responseData = $this->client->post('collect', ['json' => ['orderRef' => $orderRef]]);

        return new CollectResponse($responseData);
    }

    /**
     * @param string $orderRef Used to collect the status of the order.
     * @return bool
     * @throws ClientException
     */
    public function cancelOrder($orderRef)
    {
        $responseCode = $this->client->post('cancel', ['json' => ['orderRef' => $orderRef]])->getStatusCode();

        return $responseCode === 200;
    }
}

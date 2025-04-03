<?php

namespace Deco\Azulcargo\Model;

use Deco\Azulcargo\Api\AzulCargoAuthInterface;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Deco\Azulcargo\Helper\Data;

class AzulCargoAuth implements AzulCargoAuthInterface
{
    protected $curl;
    protected $jsonSerializer;
    protected $restRequest;
    protected $data;

    public function __construct(
        Curl $curl,
        Json $jsonSerializer,
        RestRequest $restRequest,
        Data $data
    ) {
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->restRequest = $restRequest;
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function authenticateUser(string $email, string $senha): string
    {
        $url = $this->data->getUrlProductionHomologation();
        $url = $url["Auth"];

        $payload = json_encode([
            "Email" => $email,
            "Senha" => $senha
        ]);

        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
        } else {
            $response = json_decode($response);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        curl_close($curl);

        return $response->Value;
    }
}
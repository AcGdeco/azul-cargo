<?php

namespace Deco\Azulcargo\Model;

use Deco\Azulcargo\Api\AzulCargoInterface;
use Deco\Azulcargo\Model\AzulCargoAuth;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Deco\Azulcargo\Helper\Data;

class AzulCargo implements AzulCargoInterface
{
    protected $curl;
    protected $jsonSerializer;
    protected $restRequest;
    protected $azulcargoauth;
    protected $data;

    public function __construct(
        Curl $curl,
        Json $jsonSerializer,
        RestRequest $restRequest,
        AzulCargoAuth $azulcargoauth,
        Data $data
    ) {
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->restRequest = $restRequest;
        $this->azulcargoauth = $azulcargoauth;
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function quote($email, $senha, $requestData): array
    {
        $token = $this->azulcargoauth->authenticateUser($email, $senha);

        $url = $this->data->getUrlProductionHomologation();
        $url = $url["Quote"];

        $itensData = $this->data->getItens($requestData["Itens"], $requestData["dimensionsAttributes"]);

        $payload = json_encode([
            "Token" => $token,
            "BaseOrigem" => $requestData["BaseOrigem"],
            "CEPOrigem" => $requestData["CEPOrigem"],
            "BaseDestino" => $requestData["BaseDestino"],
            "CEPDestino" => $requestData["CEPDestino"],
            "PesoCubado" => "",
            "PesoReal" => "",
            "Volume" => count($itensData["itens"]),
            "ValorTotal" => $itensData["totalPrice"],
            "Pedido" => "",
            "SiglaServico" => "",
            "TaxaColeta" => $this->data->taxaColeta($requestData["Coleta"]),
            "TipoEntrega" => $requestData["TipoEntrega"],
            "Coleta" =>  $this->data->taxaColeta($requestData["Coleta"]),
            "Itens" => $itensData["itens"]
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
            $response = json_decode($response,true);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        curl_close($curl);

        return $response;
    }
}
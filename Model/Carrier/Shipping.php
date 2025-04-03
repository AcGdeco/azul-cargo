<?php
namespace Deco\Azulcargo\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Deco\Azulcargo\Model\AzulCargo;
use Deco\Azulcargo\Helper\Data as DataHelper;

class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'azulcargo';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    protected $dataHelper;

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        AzulCargo $azulCargo,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->azulCargo = $azulCargo;
        $this->dataHelper = $dataHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @return float
     */
    private function getShippingData($requestData)
    {
        $email = $this->getConfigData('email');
        $senha = $this->getConfigData('password');

        $response = $this->azulCargo->quote($email, $senha, $requestData);

        return $response;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $dest_postcode = $request->getData('dest_postcode') ? preg_replace('/[^0-9]/', '', $request->getData('dest_postcode')): "";

        $sigla_servico = $this->getConfigData('sigla_servico');

        $weight_attribute = $this->getConfigData('weight_attribute');
        $height_attribute = $this->getConfigData('height_attribute');
        $length_attribute = $this->getConfigData('length_attribute');
        $width_attribute = $this->getConfigData('width_attribute');
        $dimensionsAttributes = [
            'weight_attribute' => $this->getConfigData('weight_attribute'),
            'height_attribute' => $this->getConfigData('height_attribute'),
            'length_attribute' => $this->getConfigData('length_attribute'),
            'width_attribute' => $this->getConfigData('width_attribute')
        ];

        $requestData = [
            "BaseOrigem" => $this->getConfigData('base_origem'),
            "CEPOrigem" => $this->getConfigData('cep_origem'),
            "BaseDestino" => $this->getConfigData('base_destino'),
            "CEPDestino" => $dest_postcode,
            "SiglaServico" => $sigla_servico,
            "TipoEntrega" => $this->getConfigData('tipo_entrega'),
            "Coleta" =>  $this->getConfigData('coleta'),
            "Itens" => $request->getData("all_items"),
            "dimensionsAttributes" => $dimensionsAttributes
        ];

        $shippingData = $this->getShippingData($requestData);

        if($shippingData["Value"] != null && $shippingData["Value"] != ""){
            foreach($shippingData["Value"] as $data){
                if ($this->dataHelper->getService($data["NomeServico"], $sigla_servico)) {
                    $method = $this->_rateMethodFactory->create();
                    
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));

                    $method->setMethod($data["NomeServico"]);
                    $method->setMethodTitle($data["NomeServico"]." - ".$data["Prazo"]." dias");

                    $price = $this->dataHelper->getPriceFee($data["Total"],$this->getConfigData('handling_fee'),$this->getConfigData('handling_type'));

                    $method->setPrice($price);
                    $method->setCost($price);

                    $result->append($method);
                }
            }
        }

        return $result;
    }
}
<?php
namespace Deco\Azulcargo\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;
    protected $storeManager;
    protected $productRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }
    
    public function getEnvironment()
    {
        return $this->scopeConfig->getValue(
            'carriers/azulcargo/environment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getUrlProductionHomologation()
    {
        $environment = $this->getEnvironment();
        
        if($environment == "Production"){
            $urls = [
                'Auth' => 'https://ediapi.onlineapp.com.br/toolkit/api/Autenticacao/AutenticarUsuario',
                'Quote' => 'https://ediapi.onlineapp.com.br/toolkit/api/Cotacao/Enviar'
            ];
        } else {
            $urls = [
                'Auth' => 'https://hmg.onlineapp.com.br/EDIv2_API_INTEGRACAO_Toolkit/api/Autenticacao/AutenticarUsuario',
                'Quote' => 'https://hmg.onlineapp.com.br/EDIv2_API_INTEGRACAO_Toolkit/api/Cotacao/Enviar'
            ];
        }

        return $urls;
    }

    public function taxaColeta($value)
    {
        if($value == 0){
            return false;
        }else{
            return true;
        }
    }

    public function getItens($products, $dimensionsAttributes)
    {
        $weight_attribute = $dimensionsAttributes['weight_attribute'];
        $height_attribute = $dimensionsAttributes['height_attribute'];
        $length_attribute = $dimensionsAttributes['length_attribute'];
        $width_attribute = $dimensionsAttributes['width_attribute'];

        $itensData["totalPrice"] = 0;
        foreach($products as $index=>$product) {
            if($product->getData("product_type") != "configurable"){
                $productRepository = $this->productRepository->getById($product->getData('product_id'));

                $itensData["itens"][] = [
                    "Volume" => $index,
                    "Peso" => $productRepository->getData($weight_attribute),
                    "Altura" => $productRepository->getData($height_attribute),
                    "Comprimento" => $productRepository->getData($length_attribute),
                    "Largura" => $productRepository->getData($width_attribute),
                    "Quantidade" => $product->getData("qty")
                ];
                $itensData["totalPrice"] += $product->getData("price");
            }
        }

        return $itensData;
    }

    public function getService($service, $serviceList)
    {
        $string = $serviceList;
        $pattern = "/(^|,)$service(,|$)/";

        if (preg_match($pattern, $string)) {
            return true;
        } else {
            return false;
        }
    }

    public function getPriceFee($total, $handling_fee, $handling_type)
    {
        if($handling_fee != null && $handling_fee != ""){
            if($handling_type == "F"){
                $price = $total + $handling_fee;
            }else{
                $price = $total + $total * $handling_fee / 100;
            }
        } else {
            $price = $total;
        }

        return $price;
    }
}
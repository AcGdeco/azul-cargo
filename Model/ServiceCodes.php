<?php

namespace Deco\Azulcargo\Model;

use Magento\Framework\Option\ArrayInterface;

class ServiceCodes implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'PREMIUM', 'label' => __('PREMIUM')],
            ['value' => 'PREMIUM_CORP', 'label' => __('PREMIUM_CORP')],
            ['value' => 'EXPRESSO', 'label' => __('EXPRESSO')],
            ['value' => 'EXPRESSO_CORP', 'label' => __('EXPRESSO_CORP')],
            ['value' => 'ECOMM', 'label' => __('ECOMM')],
            ['value' => 'ECOMM_CORP', 'label' => __('ECOMM_CORP')],
            ['value' => 'STANDARD', 'label' => __('STANDARD')],
            ['value' => 'STANDARD_CORP', 'label' => __('STANDARD_CORP')],
        ];
    }
}
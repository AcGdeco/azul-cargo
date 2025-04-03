<?php

namespace Deco\Azulcargo\Model;

use Magento\Framework\Data\OptionSourceInterface;

class Environment implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'Production', 'label' => __('Production')],
            ['value' => 'Homologation', 'label' => __('Homologation')],
        ];
    }
}
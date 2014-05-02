<?php

class Web_Rules_Model_SalesRule_Rule_Condition_Address extends Mage_SalesRule_Model_Rule_Condition_Address
{
    /**
     * Add buy_x_get_y to the sales_rule_options
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $salesRule = parent::loadAttributeOptions();
        $originalAttributes = $salesRule->getAttributeOption();

        $attributes = array(
                'buy_x_get_y' => Mage::helper('web_rules')->__('Buy X get Y from Category'),
            ) + $originalAttributes;

        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Validate rule against the buy_x_get_y
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $address = $object;
        if ('buy_x_get_y' == $this->getAttribute()) {
            $address->setByXGetY(true);
            return true;
        }

        return parent::validate($address);
    }
}
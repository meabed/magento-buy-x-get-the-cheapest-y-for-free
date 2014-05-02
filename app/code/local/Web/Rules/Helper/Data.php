<?php

class Web_Rules_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get the items the need to be discounted based on the category ids or root category and escape bundle products from calculation
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param Mage_SalesRule_Model_Rule           $rule
     * @param Mage_Sales_Model_Quote_Address      $address
     * @param Mage_Sales_Model_Quote_Address_Item $_addressItem
     *
     * @return array
     */
    public function getItemsToDiscount($rule, $address, $_addressItem)
    {
        $conditions = $rule->getConditions()->getConditions();
        $allowedCatIds = null;
        $parsedCats = array();
        $condition = null;
        $_productCategories = array();
        $cartCategories = array();
        $catToProcess = array();
        $isRootCategory = false;
        $validCondition = false;
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();

        $ignoreType = array('bundle');
        /** @var Mage_SalesRule_Model_Rule_Condition_Address $c */
        foreach ($conditions as $c) {
            if (!$allowedCatIds && !$condition && $c->getData('attribute')) {
                $condition = $c;
                $val = $c->getValueParsed();
                if (is_string($val)) {
                    $parsedCats = explode(',', $val);
                }
                if (is_array($val)) {
                    $parsedCats = $val;
                }
                if ($parsedCats && is_array($parsedCats)) {
                    if (in_array($rootCategoryId, $parsedCats)) {
                        $isRootCategory = true;
                        $_productCategories = array($rootCategoryId);
                    }
                }
            }
        }

        $items = $address->getAllVisibleItems();
        $discountStep = $rule->getDiscountStep();
        $i = 0;
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($items as $item) {
            // Escape bundles
            if (in_array($ignoreType, $item->getProductType())) {
                continue;
            }

            if (!$isRootCategory) {
                /** @var Mage_Catalog_Model_Product $_product */
                $_product = Mage::getModel('catalog/product')->load($item->getProductId());
                $_productCategories = $_product->getCategoryIds();
            }

            $validCondition = $condition->validateAttribute($_productCategories);
            $cat = array_shift($_productCategories);
            if ($isRootCategory || $validCondition) {
                for ($x = 0; $x < $item->getQty(); $x++) {
                    $cartCategories[$cat][$i]['itemId'] = $item->getId();
                    $cartCategories[$cat][$i]['qty'] = $item->getQty();
                    $cartCategories[$cat][$i]['price'] = $item->getPrice();
                    $cartCategories[$cat][$i]['item'] = $item;
                    $i++;
                    if (count($cartCategories[$cat]) >= $discountStep) {
                        $catToProcess[$cat] = $cat;
                    }
                }
            }
        }
        $x = $rule->getDiscountStep();
        $y = $rule->getDiscountAmount();
        $return = array();
        foreach ($catToProcess as $cat) {
            $catItems = $cartCategories[$cat];
            $this->aaSort($catItems, 'price');

            $qtyTobeDiscounted = (int)($i / $discountStep);
            $qtyTobeDiscounted *= $y;
            for ($j = 0; $j < $qtyTobeDiscounted; $j++) {
                $itemDisc = array_pop($catItems);
                /** @var Mage_Sales_Model_Quote_Address_Item $salesItem */
                $salesItem = $itemDisc['item'];
                if ($salesItem && !isset($return[$salesItem->getId()])) {
                    $return[$salesItem->getId()] = 0;
                }
                $return[$salesItem->getId()]++;

            }
        }
        return $return;
    }

    /**
     * Sort Multi Dimension array based on Numerical value
     *
     * @author Mohamed Meabed <mo.meabed@gmail.com>
     *
     * @param $array
     * @param $key
     */
    public function aaSort(&$array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }

        arsort($sorter, SORT_NUMERIC);

        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }

}
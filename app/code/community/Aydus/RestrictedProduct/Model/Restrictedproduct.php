<?php

/**
 * Model
 *
 * @category    Aydus
 * @package     Aydus_RestrictedProduct
 * @author      Aydus  <davidt@aydus.com>
 */

class Aydus_RestrictedProduct_Model_Restrictedproduct extends Mage_Core_Model_Abstract
{
    protected $_restrictedUser = false;

    /**
     * Check if user is restricted
     */
    protected function _construct()
    {
        $this->_restrictedUser = Mage::helper('aydus_restrictedproduct/geoip')->userIsRestricted();
    }
    
    /**
     * Check if current product is restricted
     * @return boolean
     */
    public function currentProductIsRestricted()
    {
        $currentProduct = Mage::registry('current_product');
        
        return $this->productIsRestricted($currentProduct);
    }
    
    /**
     * Check if product is restricted
     * 
     * @return boolean
     */
    public function productIsRestricted($product)
    {        
        $productId = $product->getId();
        
        if ($product && $productId){
                        
            $storeId = Mage::app()->getStore()->getId();
            
            $attributeConfig = Mage::getStoreConfig('catalog/restrictedproduct/attribute',$storeId);
            
            if ($attributeConfig){
            
                $attributeIds = explode(',', $attributeConfig);
                if (is_array($attributeIds) && count($attributeIds)>0){
            
                    foreach ($attributeIds as $attributeId){
            
                        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                        
                        if ($attribute->getId()){
                            $attributeCode = $attribute->getAttributeCode();
                            $isRestricted = $product->getResource()->getAttributeRawValue($productId, $attributeCode, $storeId);
                            
                            if ($this->_restrictedUser && $isRestricted){
                            
                                return true;
                            }                   
                                     
                        }
            
                    }
            
                }
            
            }            
            
            $attributeSetConfig = Mage::getStoreConfig('catalog/restrictedproduct/attribute_set',$storeId);
            
            if ($attributeSetConfig){
                
                $attributeSetIds = explode(',', $attributeSetConfig); 
                if (is_array($attributeSetIds) && count($attributeSetIds)>0){
                    
                    foreach ($attributeSetIds as $attributeSetId){
                        
                        if ($this->_restrictedUser && $product->getAttributeSetId() == $attributeSetId){
                            
                            return true;
                        }
                        
                    }
                    
                }
                
            }
            
            $skusConfig = Mage::getStoreConfig('catalog/restrictedproduct/skus',$storeId);
            
            if ($skusConfig){
                
                $skus = explode("\n", $skusConfig);
                if (is_array($skus) && count($skus)>0){
                
                    foreach ($skus as $sku){
                        
                        $sku = trim($sku);
                
                        if ($this->_restrictedUser && $product->getSku() == $sku){
                
                            return true;
                        }
                
                    }
                
                }                
            }
            
        }
    
        return false;
    }    
   
}
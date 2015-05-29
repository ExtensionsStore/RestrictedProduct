<?php

/**
 * Message block
 *
 * @category    Aydus
 * @package     Aydus_RestrictedProduct
 * @author      Aydus <davidt@aydus.com>
 */

class Aydus_RestrictedProduct_Block_View_Message extends Mage_Core_Block_Template
{
    
    public function showMessage()
    {
        $restricted = Mage::getSingleton('aydus_restrictedproduct/restrictedproduct')->currentProductIsRestricted();
        
        return $restricted;
    }
    
    public function getMessage()
    {
        $storeId = Mage::app()->getStore()->getId();
        $message = Mage::getStoreConfig('catalog/restrictedproduct/view_message',$storeId);
        
        return $message;
    }

}
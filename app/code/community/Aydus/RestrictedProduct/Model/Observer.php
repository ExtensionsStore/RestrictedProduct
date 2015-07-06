<?php

/**
 * Observer
 *
 * @category    Aydus
 * @package     Aydus_RestrictedProduct
 * @author      Aydus  <davidt@aydus.com>
 */

class Aydus_RestrictedProduct_Model_Observer 
{

    /**
     * 
     * @see core_block_abstract_to_html_after
     * @param Varien_Event_Observer $observer
     */
    public function showCartItemRestriction($observer)
    {
        $block = $observer->getBlock();
        
        if (get_class($block) == 'Mage_Checkout_Block_Cart_Item_Renderer'){
            
            $storeId = Mage::app()->getStore()->getId();
            $message = Mage::getStoreConfig('catalog/restrictedproduct/cart_message',$storeId);
            $item = $block->getItem();
            $product = $item->getProduct();
            $productIsRestricted = Mage::getSingleton('aydus_restrictedproduct/restrictedproduct')->productIsRestricted($product);

            if ($message && $productIsRestricted){
                
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
                $message = '<tr><td colspan="8">'.$message.'</td></tr>';
                
                $transport->setHtml($html . $message);
            }
            
        }
        
        return $observer;
        
    }

    /**
     * Remove restricted items based on shipping region
     *
     * @return array
     */
    protected function _removeRestrictedItems()
    {
        $removedItems = array();

        $request = Mage::app()->getRequest();
        $billing = $request->getParam('billing');
        
        if (isset($billing) && isset($billing['use_for_shipping']) && !$billing['use_for_shipping']){
            return $removedItems;
        }
                
        $quote = Mage::getSingleton('checkout/session')->getQuote();
    
        $address = $quote->getShippingAddress();
    
        $region = $address->getRegion();
    
        $regionRestricted = Mage::helper('aydus_restrictedproduct/geoip')->regionIsRestricted($region);
    
        if ($regionRestricted){
    
            $items = $quote->getAllItems();
    
            foreach($items as $item){
    
                $product = $item->getProduct();
                $productIsRestricted = Mage::getSingleton('aydus_restrictedproduct/restrictedproduct')->productIsRestricted($product);
    
                if ($productIsRestricted){
    
                    $itemId = $item->getId();
                    $quote->removeItem($itemId);
                    $quote->save();
    
                    $removedItems[] = $product->getName() .' ('.$product->getSku().')';
                }
    
            }
    
        }
    
        return $removedItems;
    }
    
    /**
     * On one page checkout, remove items that are restricted from shipping address
     *
     * @see controller_action_postdispatch_checkout_onepage_saveBilling | controller_action_postdispatch_checkout_onepage_saveShipping
     * @param Varien_Event_Observer $observer
     */
    public function removeRestrictedItems($observer)
    {
        $removedItems = $this->_removeRestrictedItems();
    
        if (count($removedItems)>0){
    
            $storeId = Mage::app()->getStore()->getId();
            $message = Mage::getStoreConfig('catalog/restrictedproduct/checkout_message',$storeId);
    
            if (!$message){
                $message = 'The following products have been removed from your cart: ';
            }
    
            $message .= implode(',', $removedItems);
    
            $event = $observer->getEvent();
            $controller = $event->getControllerAction();
            $response = $controller->getResponse();
    
            $result['error'] = true;
            $result['message'] = $message;
            $response->setBody(Mage::helper('core')->jsonEncode($result));
        }
    
        return $observer;
         
    }
    
    /**
     * On paypal review, remove items that are restricted from shipping address
     *
     * @see controller_action_predispatch_paypaluk_express_review
     * @param Varien_Event_Observer $observer
     */
    public function paypalRemoveRestrictedItems($observer)
    {
        $removedItems = $this->_removeRestrictedItems();
    
        if (count($removedItems)>0){
    
            $storeId = Mage::app()->getStore()->getId();
            $message = Mage::getStoreConfig('catalog/restrictedproduct/checkout_message',$storeId);
    
            if (!$message){
                $message = 'The following products have been removed from your cart: ';
            }
    
            $message .= implode(',', $removedItems);
    
            $session = Mage::getSingleton('core/session');
            $session->addError($message);
        }
    
        return $observer;
    }
   
}
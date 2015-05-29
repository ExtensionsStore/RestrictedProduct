<?php

/**
 * System configuration attribute source
 *
 * @category    Aydus
 * @package     Aydus_RestrictedProduct
 * @author      Aydus <davidt@aydus.com>
 */

class Aydus_Restrictedproduct_Model_Adminhtml_System_Config_Source_Attribute
{

    /**
     * Get attribute options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();
        $options = array();
        
        if ($collection->getSize()>0){
            
            $options[] = array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--None--'));
            foreach ($collection as $attribute){
                
                if ($attribute->getFrontendLabel()){
                    
                    $options[] = array('value' => $attribute->getId(), 'label'=> $attribute->getFrontendLabel());
                }
            
            }
                        
        }

        return $options;
    }


}

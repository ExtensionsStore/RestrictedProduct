<?php

/**
 * System configuration attribute set source
 *
 * @category    Aydus
 * @package     Aydus_RestrictedProduct
 * @author      Aydus <davidt@aydus.com>
 */

class Aydus_Restrictedproduct_Model_Adminhtml_System_Config_Source_Attributeset
{

    /**
     * Get attribute set options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributeSets = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeSetCollection();
        
        $options = array();
        $options[] = array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--None--'));
        
        $options = array_merge($options, $attributeSets->toOptionArray());
        
        return $options;
    }


}

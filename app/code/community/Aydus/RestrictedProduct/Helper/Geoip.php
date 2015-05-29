<?php

/**
 * Geoip Helper
 *
 * @category    Aydus
 * @package     Aydus_RestrictedProduct
 * @author      Aydus <davidt@aydus.com>
 */

class Aydus_RestrictedProduct_Helper_Geoip extends Mage_Core_Helper_Abstract
{
    protected $_userIsRestricted;
    protected $_geoip;
    
    /**
     * Load geoip database file
     */
    public function __construct()
    {
        try {
            	
            //switch to module root
            $dir = __DIR__;
            chdir($dir.DS.'..');
            	            	
            //open database file
			include_once('lib'.DS.'geoipcity.inc');
            $path = 'lib'.DS.'GeoLiteCity.dat';
            $this->_geoip = geoip_open($path, GEOIP_STANDARD);
            
            //back to magento root
            chdir(Mage::getBaseDir());

            
            $record = $this->getRecord();
            
            //check if user's region is restricted
            $this->_userIsRestricted = $this->regionIsRestricted($record->region);
                        
    
        } catch(Exception $e){
            	
            Mage::log($e->getMessage(),null,'aydus_restrictedproduct.log');
        }
    
    }
    
    /**
     * Check if region is restricted
     * 
     * @param string $region
     * @return boolean
     */
    public function regionIsRestricted($region)
    {
        
        $storeId = Mage::app()->getStore()->getId();
        $restrictedRegionConfig = Mage::getStoreConfig('catalog/restrictedproduct/restricted_regions',$storeId);
        
        if ($restrictedRegionConfig){
        
            $restrictedRegionIds = explode(',', $restrictedRegionConfig);
            if (is_array($restrictedRegionIds) && count($restrictedRegionIds)>0 && $restrictedRegionIds[0]){
        
                foreach ($restrictedRegionIds as $restrictedRegionId){
        
                    $restrictedRegion = Mage::getModel('directory/region')->load($restrictedRegionId);
                    $regionCode = $restrictedRegion->getCode();
                    $regionName = $restrictedRegion->getDefaultName();
                    
                    if ($region == $regionCode || $region == $regionName){
        
                        return true;
                    }
        
                }
        
            }
        
        }        
        
        return false;
    }
    
    public function userIsRestricted()
    {
        return $this->_userIsRestricted;
    }
    
    /**
     * Get user's geoip record
     *
     * @param string $ip
     * @return geoiprecord|boolean
     */
    public function getRecord($ip = null)
    {
        try {
            	
            if (!$ip){
                $ip = $this->getIp();
            }
            	
            $record = geoip_record_by_addr($this->_geoip, $ip);
            $record = $this->fixObject($record);
            return $record;
    
        }catch (Exception $e){
    
            Mage::log($e->getMessage(),null,'aydus_restrictedproduct.log');
        }
    
        return false;
    }
    
    /**
     *
     * @param string $long
     * @return Ambigous <number, string>
     */
    public function getIp($long = false)
    {
        $request = Mage::app()->getRequest();
    
        if ($request->getServer('HTTP_CLIENT_IP')){
            $ip = $request->getServer('HTTP_CLIENT_IP');
        } else if ($request->getServer('HTTP_X_FORWARDED_FOR')){
            $ip = $request->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $request->getServer('REMOTE_ADDR');
        }
    
        if ($long){
    
            $ip = ip2long($ip);
        }
    
        return $ip;
    }
    
    /**
     *
     * @param unknown $object
     * @see http://stackoverflow.com/questions/965611/forcing-access-to-php-incomplete-class-object-properties
     * @return mixed
     */
    public function fixObject(&$object)
    {
        if (!is_object ($object) && gettype ($object) == 'object'){
    
            return ($object = unserialize (serialize ($object)));
        }
    
        return $object;
    }
}
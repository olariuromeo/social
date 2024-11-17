<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaCore UNA Core
 * @{
 */

class BxDolCacheMemcache extends BxDolCache
{
    protected $iTTL = 3600;
    protected $iStoreFlag = 0;
    protected $oMemcache = null;

    /**
     * constructor
     */
    function __construct()
    {
        parent::__construct();
        if (class_exists('Memcache')) {
            $this->oMemcache = new Memcache();
            $sHost = getParam('sys_cache_memcache_host');
            if (false === strpos($sHost, ',')) {
                if (!$this->oMemcache->connect (getParam('sys_cache_memcache_host'), getParam('sys_cache_memcache_port')))
                    $this->oMemcache = null;
            } 
            else {
                // setting memcache.hash_strategy = consistent is recommended
                $aHosts = explode(',', $sHost);
                if ($aHosts) {
                    foreach ($aHosts as $s)
                        $this->oMemcache->addServer(trim($s), getParam('sys_cache_memcache_port'));
                } 
                else {
                    $this->oMemcache = null;
                }
            }
        }
    }

    /**
     * Get data from cache server
     *
     * @param  string $sKey - file name
     * @param  int    $iTTL - time to live
     * @return the    data is got from cache.
     */
    function getData($sKey, $iTTL = false)
    {
        $mixedData = $this->oMemcache->get($sKey);
        return false === $mixedData ? null : $mixedData;
    }

    /**
     * Save data in cache server
     *
     * @param  string  $sKey      - file name
     * @param  mixed   $mixedData - the data to be cached in the file
     * @param  int     $iTTL      - time to live
     * @return boolean result of operation.
     */
    function setData($sKey, $mixedData, $iTTL = false)
    {
        return $this->oMemcache->set($sKey, $mixedData, $this->iStoreFlag, false === $iTTL ? $this->iTTL : $iTTL);
    }

    /**
     * Delete cache from cache server
     *
     * @param  string $sKey - file name
     * @return result of the operation
     */
    function delData($sKey)
    {
        $this->oMemcache->delete($sKey);
        return true;
    }

    /**
     * Check if memcache is available
     * @return boolean
     */
    function isAvailable()
    {
        return $this->oMemcache == null ? false : true;
    }

    /**
     * Check if memcache extension is loaded
     * @return boolean
     */
    function isInstalled()
    {
        return extension_loaded('memcache');
    }

    /**
     * It always removes all data, regardless the prefix param
     * @return true on success
     */
    function removeAllByPrefix ($s)
    {
        return $this->oMemcache->flush();
    }
}

/** @} */

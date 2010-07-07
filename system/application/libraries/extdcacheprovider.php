<?php

class ExtDCacheProvider {
    var $_filePath = null;
    var $_cache = false;
    
    function ExtDCacheProvider($params) {
        if(is_string($params['filePath'])) {
            $this->_filePath = APPPATH.$params['filePath'];
            
            if(!file_exists(APPPATH.$params['filePath']) && !touch(APPPATH.$params['filePath'])) {
                throw new Exception('Unable to create or access ' . $params['filePath']);
            }
        }
    }
    
    function getAPI() {
        $this->_parse();        
        return $this->_cache['api'];
    }
    
    function isModified($apiInstance) {
        $this->_parse();
        if(!$apiInstance instanceof ExtDAPI) {
            throw new Exception('You have to pass an instance of ExtDirect_API to isModified function');
        }
        return !(
            $apiInstance->isEqual($this->_cache['classes'], $apiInstance->getClasses()) &&
            // even if the classes are the same we still have to check if they have been modified
            $apiInstance->isEqual($this->_cache['modifications'], $this->_getModifications($apiInstance))
        );
    }

    function save($apiInstance) {
        if(!$apiInstance instanceof ExtDAPI) {
            throw new Exception('You have to pass an instance of ExtDirect_API to save function');
        }
        $cache = json_encode(array(
            'classes' => $apiInstance->getClasses(),
            'api' => $apiInstance->getAPI(),
            'modifications' => $this->_getModifications($apiInstance)
        ));
        $this->_write($cache);  
    }
    
    function _getModifications($apiInstance) {
        if(!$apiInstance instanceof ExtDAPI) {
            throw new Exception('You have to pass an instance of ExtDirect_API to _getModifications function');
        }
        
        $modifications = array();
        $classesPaths = $apiInstance->getClassesPaths();
        
        foreach($classesPaths as $path) {
            if(file_exists($path)) {
                $modifications[$path] = filemtime($path);
            }
        }
        return $modifications;
    }
    
    function _write($content = '', $append = false) {
        file_put_contents($this->_filePath, $content, $append ? FILE_APPEND : 0);
    }
    
    function _parse() {
         if($this->_cache === false) {
             $content = file_get_contents($this->_filePath);
             if(strlen($content) === 0) {
                 $this->_cache = array(
                     'classes' => array(), 
                     'api' => array(), 
                     'modifications' => array()
                 );
                 return;
             }
            
             $this->_cache = json_decode($content, true);            
        }       
    }
}
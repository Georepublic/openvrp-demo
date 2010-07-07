<?php
class ExtDAPI {
	var $_routerUrl = 'router.php';
	var $_cacheProvider = null;
	var $_defaults = array();
	var $_classes = array();
	var $_remoteAttribute = '@remotable';
	var $_formAttribute = '@formHandler';
	var $_nameAttribute = '@remoteName';
	var $_namespace = false;
	var $_type = 'remoting';
	var $_parsedClasses = array();
	var $_parsedAPI = array();
	var $_descriptor = 'Ext.app.REMOTING_API';
	
	function ExtDAPI() {
	}
	
	function getState() {
		return array(
			'routerUrl' => $this->getRouterUrl(),
			'defaults' => $this->getDefaults(),
			'classes' => $this->getClasses(),
			'remoteAttribute' => $this->getRemoteAttribute(),
			'formAttribute' => $this->getFormAttribute(),
			'nameAttribute' => $this->getNameAttribute(),
			'namespace' => $this->getNamespace(),
			'parsedAPI' => $this->_parsedAPI,
			'descriptor' => $this->_descriptor
		);
	}
	
	function setState($state) {
		if(isset($state['routerUrl'])) {
			$this->setRouterUrl($state['routerUrl']);
		}
		
		if(isset($state['defaults'])) {
			$this->setDefaults($state['defaults']);
		}
		
		if(isset($state['classes'])) {
			$this->_classes = $state['classes'];
		}
		
		if(isset($state['remoteAttribute'])) {
			$this->setRemoteAttribute($state['remoteAttribute']);
		}
		
		if(isset($state['formAttribute'])) {
			$this->setFormAttribute($state['formAttribute']);
		}

		if(isset($state['nameAttribute'])) {
			$this->setFormAttribute($state['nameAttribute']);
		}
				
		if(isset($state['namespace'])) {
			$this->setNameSpace($state['namespace']);
		}

		if(isset($state['descriptor'])) {
			$this->setDescriptor($state['descriptor']);
		}
		
		if(isset($state['parsedAPI'])) {
			$this->_parsedAPI = $state['parsedAPI'];
		}	  
	}
	
	function add($classes = array(), $settings = array()) {
		$settings = array_merge(
			array(
				'autoInclude' => false,
				'basePath' => '',
				'seperator' => '_',
				'prefix' => '',
				'subPath' => ''
			), 
			$this->_defaults,
			$settings
		);
		
		if(is_string($classes)) {
			$classes = array($classes);
		}

		foreach($classes as $name => $cSettings) {
			if(is_int($name)) {
				$name = $cSettings;
				$cSettings = array();
			}
			$cSettings = array_merge($settings, $cSettings);
			$cSettings['fullPath'] = $this->getClassPath($name, $cSettings);
			$this->_classes[$name] = $cSettings;
		}
	}
	
	function output($print = true) {
		$saveInCache = false;
		if(isset($this->_cacheProvider)) {
			if(!$this->_cacheProvider->isModified($this)) {
				$api = $this->_cacheProvider->getAPI();
				if($print === true) $this->_print($api);
				$this->_parsedClasses = $this->_classes;
				$this->_parsedAPI = $api;  
				return $api;
			}
			$saveInCache = true;
		}		   
		
		$api = $this->getAPI();
		
		if($saveInCache) {
			$this->_cacheProvider->save($this);
		}
		
		if($print === true) $this->_print($api);
		return $api;
	}
	
	function isEqual($old, $new) {
		return serialize($old) === serialize($new);
	}
	
	function getAPI() {
		if($this->isEqual($this->_classes, $this->_parsedClasses)) {
			return $this->getParsedAPI();
		}
		
		$classes = array();

		foreach($this->_classes as $class => $settings) {
			$methods = array();
			
			if($settings['autoInclude'] === true) {
				$path = !$settings['fullPath']
					? $this->getClassPath($class, $settings)
					: $settings['fullPath'];
					
				if(file_exists($path)) {
					require_once($path);
				}
			}

			// here the reflection magic begins
			if(class_exists($settings['prefix'] . $class)) { 
				$rClass = new ReflectionClass($settings['prefix'] . $class);
				$rMethods = $rClass->getMethods();
				foreach($rMethods as $rMethod) {
					if(
						$rMethod->isPublic() &&
						strlen($rMethod->getDocComment()) > 0
					) {
						$doc = $rMethod->getDocComment();
						$isRemote = !!preg_match('/' . $this->_remoteAttribute . '/', $doc);					   
						if($isRemote) {
							$method = array(
								'name' => $rMethod->getName(),
								'len' => $rMethod->getNumberOfParameters(),
							);
							if(!!preg_match('/' . $this->_nameAttribute . ' ([\w]+)/', $doc, $matches)) {
								$method['serverMethod'] = $method['name'];
								$method['name'] = $matches[1];
							}					   
							if(!!preg_match('/' . $this->_formAttribute . '/', $doc)) {
								$method['formHandler'] = true;
							}

							$methods[] = $method;
						}
					}
				}
				
				if(count($methods) > 0) {
					$classes[$class] = $methods;
				}		  
			}
		}
		
		$api = array(
			'url' => $this->_routerUrl,
			'type' => $this->_type,
			'actions' => $classes
		);
		
		if($this->_namespace !== false) {
			$api['namespace'] = $this->_namespace;
		}
		
		$this->_parsedClasses = $this->_classes;
		$this->_parsedAPI = $api;
		
		return $api;
	}
	
	function getParsedAPI() {
		return $this->_parsedAPI;
	}
	
	function getClassPath($class, $settings = false) {
		if(!$settings) {
			$settings = $this->_settings;
		}
		
		if($settings['autoInclude'] === true) {
			$path = $settings['basePath'] . DIRECTORY_SEPARATOR .
					$settings['subPath'] . DIRECTORY_SEPARATOR .
					$class . '.php';
			$path = str_replace('\\\\', '\\', $path);			
		} else {
			$rClass = new ReflectionClass($settings['prefix'] . $class);
			$path = $rClass->getFileName();
		}
		
		return APPPATH.$path;
	}
	
	function getClassesPaths() {
		$classesPaths = array();
		foreach($this->getClasses() as $name => $settings) {
			$classesPaths[] = $this->getClassPath($name, $settings);
		}
		
		return $classesPaths;
	}
	
	function getClasses() {
		return $this->_classes;
	}
	
	function _print($api) {
		header('Content-Type: text/javascript');


		echo ($this->_namespace ? 
			'Ext.ns(\'' . substr($this->_descriptor, 0, strrpos($this->_descriptor, '.')) . '\'); ' . $this->_descriptor:
			'Ext.ns(\'Ext.app\'); ' . 'Ext.app.REMOTING_API'
		);
		echo ' = ';
		echo json_encode($api);
		echo ';';
	}
	
	function setRouterUrl($routerUrl = 'router.php') {
		if(isset($routerUrl)) {
			$this->_routerUrl = $routerUrl;
		}
	}
	
	function getRouterUrl() {
		return $this->_routerUrl;
	}
	
	function setCacheProvider($cacheProvider) {
		if($cacheProvider instanceof ExtDCacheProvider) {
			$this->_cacheProvider = $cacheProvider;
		}
	}

	function getCacheProvider() {
		return $this->_cacheProvider;
	}
		
	function setRemoteAttribute($attribute) {
		if(is_string($attribute) && strlen($attribute) > 0) {
			$this->_remoteAttribute = $attribute;
		}
	}

	function getRemoteAttribute() {
		return $this->_remoteAttribute;
	}
 
	 function setDescriptor($descriptor) {
		if(is_string($descriptor) && strlen($descriptor) > 0) {
			$this->_descriptor = $descriptor;
		}
	}

	function getDescriptor() {
		return $this->_descriptor;
	}
		
	function setFormAttribute($attribute) {
		if(is_string($attribute) && strlen($attribute) > 0) {
			$this->_formAttribute = $attribute;
		}
	}

	function getFormAttribute() {
		return $this->_formAttribute;
	}

	function setNameAttribute($attribute) {
		if(is_string($attribute) && strlen($attribute) > 0) {
			$this->_nameAttribute = $attribute;
		}
	}

	function getNameAttribute() {
		return $this->_nameAttribute;
	}
			   
	function setNameSpace($namespace) {
		if(is_string($namespace) && strlen($namespace) > 0) {
			$this->_namespace = $namespace;
		}
	}

	function getNamespace() {
		return $this->_namespace;
	}
		
	function setDefaults($defaults, $clear = false) {
		if($clear === true) {
			$this->clearDefaults();
		}
		
		if(is_array($defaults)) {
			$this->_defaults = array_merge($this->_defaults, $defaults);
		}
	}
	
	function getDefaults() {
		return $this->_defaults;	
	}
	
	function clearDefaults() {
		$this->_defaults = array();
	}
}

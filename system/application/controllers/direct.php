<?php

class Direct extends Controller {

	function Direct()
	{
		parent::Controller();
		$this->load->library('extdapi');
		$this->load->library('extdcacheprovider', array('filePath' => 'cache/api_cache.txt'));
	}
	
	function api($output = true)
	{
		$this->extdapi->setRouterUrl('/direct/router'); // default
		$this->extdapi->setCacheProvider($this->extdcacheprovider);
		$this->extdapi->setNamespace('Ext.app');
		$this->extdapi->setDescriptor('Ext.app.REMOTING_API');
		$this->extdapi->setDefaults(array(
		    'autoInclude' => true,
		    'basePath' => 'libraries'
		));
		
		$this->extdapi->add(
			array(
				'Echo' => array('prefix' => 'Class_'),
				'Exception' => array('prefix' => 'Class_'),
				'Time',
				'File'
			)
		);

		if($output) $this->extdapi->output();
		$this->session->set_userdata(array('ext-direct-state' => $this->extdapi->getState()));
	}
	
	function router()
	{
		if(!$this->session->userdata('ext-direct-state')) {
			$this->api(false);
		}
		$this->load->library('extdrouter', array('api' => $this->extdapi));
		$this->extdrouter->dispatch();
		$this->extdrouter->getResponse(true);
	}
}

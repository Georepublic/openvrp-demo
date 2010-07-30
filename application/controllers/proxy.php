<?php

class Proxy extends Controller {

	function Proxy()
	{
		parent::Controller();		
        //$this->load->library('curl');
	}

	function index()
	{
		$this->output->set_output("Error: bad request.");
	}
	
	function url()
	{
		//print_r($this->uri->segment_array());
    	//echo $request = $this->input->post('url', TRUE);
		//$response = $this->curl->simple_get(echo $_POST['url']);
		//echo $response;

		$url = $_POST['url'];
		$this->output->set_output($url);

		$session = curl_init($url);
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		$this->output->set_header("Content-Type: application/jsonrequest");				
		$this->output->set_output(curl_exec($session));
		curl_close($session);
	}

}

?>

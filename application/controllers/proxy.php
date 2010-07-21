<?php

class Proxy extends Controller {

	function Proxy()
	{
		parent::Controller();		
        $this->load->library('curl');
	}

	function index()
	{
    	echo "No proxy request.";
	}
	
	function url()
	{
    	//echo "POST request.";
		//print_r($this->uri->segment_array());
    	//echo $request = $this->input->post('url', TRUE);
		//$response = $this->curl->simple_get(echo $_POST['url']);
		//echo $response;
	}

}

?>

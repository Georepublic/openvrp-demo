<?php

class Main extends Controller {

	function Main()
	{
		parent::Controller();
		
	    if($this->session->userdata('logged_in') != TRUE)
	    {
	        redirect('login');
	    }
	}

	function index()
	{
		$data['title']    = "Hello World";
		$data['account']  = $this->session->userdata('username');
	    $data['language'] = 'en';
		
		$this->load->view('main_view', $data);
	}

	function download()
	{
		//$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		//$this->output->set_header("Content-Type: application/jsonrequest");		
		//$this->output->set_header("Content-Type: application/vnd.google-earth.kml+xml");		
		//$this->output->set_output($this->uri->segment(3));
		//$this->load->helper('download');		
		//force_download('openvrp.kml', $kml = $this->uri->segment(3));
		header('Cache-Control: maxage=120'); 
		header('Expires: '.date(DATE_COOKIE,time()+120)); // Cache for 2 mins 
		header('Pragma: public'); 
		header('Content-type: application/force-download');  
		header('Content-Transfer-Encoding: Binary');  
		header('Content-Type: application/octet-stream');  
		header('Content-Disposition: attachment; filename="openvrp.kml"'); 
		$this->output->set_output(file_get_contents($this->uri->segment(3)));
	}

}

?>


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

}

?>


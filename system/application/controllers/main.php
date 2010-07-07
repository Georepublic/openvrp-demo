<?php

class Main extends Controller {

	function Main()
	{
		parent::Controller();
	}

	function index()
	{
	    if ($this->session->userdata('logged_in') != TRUE)
	    {
	        redirect('login/index');
	    }

		$data['title']   = "Hello World";
		$data['account'] = $this->session->userdata('username');
		$data['todo']    = array('Germany', 'Spain', 'Netherlands');
		
		$this->load->view('main', $data);
	}
}

?>


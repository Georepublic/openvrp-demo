<?php

class Main extends Controller {

	function Main()
	{
		parent::Controller();
		
		$this->load->helper('url');
	}

	function index()
	{
		$data['title']   = "Hello Title";
		$data['heading'] = "Hello Heading";
		$data['todo']    = array('Germany', 'Spain', 'Netherlands');
		
		$this->load->view('main_view', $data);
	}
}


?>

<?php

class Route extends Controller {

	function Route()
	{
		parent::Controller();

	    if($this->session->userdata('logged_in') != TRUE)
	    {
	        redirect('login');
	    }

		$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		//$this->output->set_header("Content-Type: application/jsonrequest");		
	}

	function index()
	{
		//$this->load->view('report_view', $data);
		echo "route";
	}

	function euclidian()
	{
		echo "euclidian";
	}

	function spheroid()
	{
		echo "spheroid";
	}

	function dijkstra()
	{
		echo "dijkstra";
	}

	function astar()
	{
		echo "astar";
	}

	function shooting()
	{
		echo "shooting";
	}
}


?>

<?php

class Report extends Controller {

	function Report()
	{
		parent::Controller();

	    if($this->session->userdata('logged_in') != TRUE)
	    {
	        redirect('login');
	    }

		$this->output->set_header("Cache-Control: no-cache, must-revalidate");
		$this->output->set_header("Content-Type: application/jsonrequest");		
	}

	function index()
	{
		//$this->load->view('report_view', $data);
		echo "report";
	}

	function create()
	{
		echo "create";
	}

	function update()
	{
		echo "update";
	}

	function delete()
	{
		echo "delete";
	}
}


?>

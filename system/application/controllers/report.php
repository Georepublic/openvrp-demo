<?php

class Report extends Controller {

	function Report()
	{
		parent::Controller();
		
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

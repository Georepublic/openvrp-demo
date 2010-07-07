<?php

class Vehicle extends Controller {

	function Vehicle()
	{
		parent::Controller();
		
		$this->load->scaffolding('vehicle');
	}

	function index()
	{
		//$this->load->view('vehicle_view', $data);
		echo "vehicle";
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

<?php

class Depot extends Controller {

	function Depot()
	{
		parent::Controller();
		
		$this->load->scaffolding('depot');
	}

	function index()
	{
		//$this->load->view('depot_view', $data);
		echo "depot";
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

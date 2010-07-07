<?php

class Order extends Controller {

	function Order()
	{
		parent::Controller();
		
		$this->load->scaffolding('request');
	}

	function index()
	{
		//$this->load->view('order_view', $data);
		echo "order";
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

<?php

class Account extends Controller {

	function Account()
	{
		parent::Controller();
		
		$this->load->scaffolding('account');
	}

	function index()
	{
		//$this->load->view('account_view', $data);
		echo "account";
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

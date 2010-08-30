<?php

class Account extends Controller {

	function Account()
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
		$this->output->set_output("Error: bad request.");
	}

	/**
	 * Get account(s)
	 */ 
	function find()
	{
		if($this->uri->segment(3) !== FALSE)
		{
			$this->db->where('id', $id = $this->uri->segment(3));
		}
		
		$query = $this->db->get('account');
		$this->output->set_output($this->_encode($query));
	}

	/**
	 * Create account
	 */ 
	function create() {
		$data = array(
		   'name'    => $this->input->post('name'),
		   'pass'    => md5($this->input->post('pass'))
		);
		$this->db->set($data); 
		$this->db->insert('account'); 
		$this->output->set_output("{success: true}");
	}

	/**
	 * Update account
	 */ 
	function update() { 
		$data = array(
		   'name'    => $this->input->post('name'),
		   'pass'    => md5($this->input->post('pass')),
		   'updated' => 'now()'
		);		
		$this->db->where('id', $id = $this->uri->segment(3));
		$this->db->update('account', $data); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Delete account
	 */ 
	function delete() { 
		$this->db->where('id', $id = $this->uri->segment(3));
		$this->db->delete('account'); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Build JSON object for "account"
	 */ 
	function _encode($query)
	{
		$json = array(
			"type"     => "FeatureCollection",
			"features" => array()
		);
		
		foreach ($query->result() as $row)
		{
			$feature = array(
				//"geometry"   => json_decode($row->geometry),
				"type"       => "Feature",
				"properties" => array(
					"id"       	=> $row->id,
					"name" 		=> $row->name,
					"created"  	=> $row->created,
					"updated"  	=> $row->updated
				),
				"id"         => $row->id
			);
			
			array_push($json["features"], $feature);
		}
		
		return json_encode($json);
	}
}

?>

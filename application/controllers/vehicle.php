<?php

class Vehicle extends Controller {

	function Vehicle()
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
	 * Get feature(s)
	 */ 
	function find()
	{
		if($this->uri->segment(3) !== FALSE)
		{
			$this->db->where('vehicle_id', $id = $this->uri->segment(3));
		}
		
		$query = $this->db->get('vehicle');
		$this->output->set_output($this->_encode($query));
	}

	/**
	 * Create feature
	 */ 
	function create() {
		$data = array(
		   'name'   => $this->input->post('name'),
		   'capacity' => $this->input->post('capacity'),
		   'depot_id' => $this->input->post('depot_id')
		);
		$this->db->set($data); 
		$this->db->insert('vehicle'); 
		$this->output->set_output("{success: true}");
	}

	/**
	 * Update feature
	 */ 
	function update() { 
		$data = array(
		   'name'   => $this->input->post('name'),
		   'capacity' => $this->input->post('capacity'),
		   'depot_id' => $this->input->post('depot_id'),
		   'updated' => 'now()'
		);
		$this->db->where('vehicle_id', $id = $this->uri->segment(3));
		$this->db->update('vehicle', $data); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Delete feature
	 */ 
	function delete() { 
		$this->db->where('vehicle_id', $id = $this->uri->segment(3));
		$this->db->delete('vehicle'); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Build GeoJSON object for "vehicle"
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
				"type"        => "Feature",
				"properties"  => array(
					"vehicle_id" => $row->vehicle_id,
					"name"       => $row->name,
					"capacity"   => $row->capacity,
					"depot_id"   => $row->depot_id,
					"created"    => $row->created,
					"updated"    => $row->updated
				),
				"vehicle_id"     => $row->vehicle_id
			);
			
			array_push($json["features"], $feature);
		}
		
		return json_encode($json);
	}
}


?>

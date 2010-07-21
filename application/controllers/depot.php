<?php

class Depot extends Controller {

	function Depot()
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
			$this->db->where('id', $id = $this->uri->segment(3));
		}
		
		$query = $this->db->get('json_depots');
		$this->output->set_output($this->_encode($query));
	}

	/**
	 * Create feature
	 */ 
	function create() {
		$sql = "INSERT INTO depot(name,the_geom) VALUES( ?, ST_transform(ST_GeometryFromText( ? ,900913),4326))";
		$data = array(
		   'name'   => $this->input->post('name'),
		   'the_geom' => $this->input->post('wkt')
		);
		$this->db->query($sql, $data); 
		$this->output->set_output("{success: true}");
	}

	/**
	 * Update feature
	 */ 
	function update() { 
		$sql = "UPDATE depot SET name = ?, the_geom = ST_transform(ST_GeometryFromText( ? ,900913),4326), updated = ? WHERE id = ?";
		$data = array(
		   'name'   => $this->input->post('name'),
		   'the_geom' => $this->input->post('wkt'),
		   'updated' => 'now()',
		   'id' => $this->uri->segment(3)
		);
		$this->db->query($sql, $data); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Delete feature
	 */ 
	function delete() { 
		$this->db->where('id', $id = $this->uri->segment(3));
		$this->db->delete('depot'); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Build GeoJSON object for "depot"
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
				"geometry"    => json_decode($row->geometry),
				"type"        => "Feature",
				"properties"  => array(
					"id"      => $row->id,
					"name"    => $row->name,
					"wkt"     => $row->wkt,
					"created" => $row->created,
					"updated" => $row->updated
				),
				"id"          => $row->id
			);
			
			array_push($json["features"], $feature);
		}
		
		return json_encode($json);
	}
}


?>

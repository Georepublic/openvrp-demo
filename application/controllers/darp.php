<?php
class Darp extends Controller {

	function Darp()
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
		$this->output->set_output("Error: bad request.");
	}

	/**
	 * DARP calculation
	 */ 
	function calculate()
	{
		$data = array( 
			'depot_id' => $this->input->post('depot_id'),
			'method'   => $this->input->post('method')
		);
		$this->_buildmatrix($data);
		
		$sql = "SELECT id, order_id, vehicle_id, pick_up, to_char(at, 'YYYY-MM-DD HH24:MI:SS'::text) AS at
					FROM darp(
						'SELECT * FROM darp_orders WHERE depot_id IN (0,".$data['depot_id'].")',
						'SELECT * FROM darp_vehicles WHERE depot_id = ".$data['depot_id']."', 
						'SELECT * FROM distances'
				);";
		
		$query = $this->db->query($sql); 
		$this->output->set_output($this->_encode($query));
	}

	/**
	 * PUBLIC: Calculate distance wrapper
	 */ 
	function distance() {
		$data = array( 
			'depot_id' => $this->input->post('depot_id'),
			'method'   => 'euclidian'
		);
		
		$this->output->set_output($this->_buildmatrix($data));
	}
	
	/**
	 * PRIVATE: Build distance matrix for "darp"
	 */ 
	function _buildmatrix($data)
	{
		// Drop table content
		$this->db->empty_table('distances'); 
		
		// Renumber order_id
		$this->db->query('ALTER TABLE orders ADD COLUMN temp_id serial;');
		$this->db->query('UPDATE orders SET id=temp_id;');
		$this->db->query('ALTER TABLE orders DROP COLUMN temp_id;');
		
		// Renumber vehicle_id
		$this->db->query('ALTER TABLE vehicle ADD COLUMN temp_id serial;');
		$this->db->query('UPDATE vehicle SET id=temp_id;');
		$this->db->query('ALTER TABLE vehicle DROP COLUMN temp_id;');
		
		// Select all orders with one depot.
		$this->db->where('depot_id', $data['depot_id']);
		$this->db->or_where('depot_id', 0 ); 
		$query = $this->db->get('darp_points');
		
		$sql = null;

		foreach ($query->result() as $a)
		{	
			foreach ($query->result() as $b)
			{
				switch($data['method']) {
					case 'euclidian' : 				
						$sql .= "INSERT INTO distances(from_order,to_order,value) 
									VALUES (" . $a->id . "," . $b->id . "," . 
									"round(ST_distance('" . $a->geom_meter . "','" . $b->geom_meter . "') / 5.55));\n";
						break;	
				}					
			}
		}
		$this->db->query($sql); 
				
		return "{success: true}";
	}

	/**
	 * Build GeoJSON object for "darp"
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
				//"geometry"    => json_decode($row->geometry),
				"type"        => "Feature",
				"properties"  => array(
					"id"         => $row->id,
					"order_id"   => $row->order_id,
					"vehicle_id" => $row->vehicle_id,
					"pick_up"    => $row->pick_up,
					"at"         => $row->at
				),
				"id"          => $row->id
			);
			
			array_push($json["features"], $feature);
		}
		
		return json_encode($json);
	}
}
?>
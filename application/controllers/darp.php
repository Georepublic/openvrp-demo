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
		$this->output->set_header("Content-Type: application/jsonrequest");		
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
			'nearest'  => $this->input->post('nearest'),
			'method'   => $this->input->post('method')
		);
		
		$sql = "SELECT b.*, a.id, a.order_id, vehicle_id, pick_up, to_char(at, 'YYYY-MM-DD HH24:MI:SS'::text) AS at
					FROM darp(
						'SELECT * FROM darp_order',
						'SELECT * FROM darp_vehicle', 
						'".$distance = $this->_getdistances($data)."',
						'depot_id','depot_point',
						'SELECT * FROM penalties LIMIT 1'
				) a LEFT JOIN (
					SELECT * FROM darp_report WHERE depot_id IN (0,".$data['depot_id'].")
				) AS b ON (a.order_id = b.id);";
		
		$query = $this->db->query($sql); 
		$this->output->set_output($this->_encode($query));		
	}

	/**
	 * DARP debug SQL
	 */ 
	function debugsql()
	{
		$data = array( 
			'depot_id' => $this->input->post('depot_id'),
			'nearest'  => $this->input->post('nearest'),
			'method'   => $this->input->post('method')
		);
		
		$sql = "SELECT b.*, a.id, a.order_id, vehicle_id, pick_up, to_char(at, 'YYYY-MM-DD HH24:MI:SS'::text) AS at
					FROM darp(
						'SELECT * FROM darp_orders WHERE depot_id IN (0,".$data['depot_id'].")',
						'SELECT * FROM darp_vehicles WHERE depot_id = ".$data['depot_id']."', 
						'".$distance = $this->_getdistances($data)."'
				) a LEFT JOIN (SELECT * FROM darp_report WHERE depot_id IN (0,".$data['depot_id'].")) AS b ON (a.order_id = b.id);";
		
		$this->output->set_header("Content-Type: application/octet-stream");		
		$this->output->set_output($sql);
	}

	/**
	 * PRIVATE: Build distance matrix for "darp"
	 */ 
	function _getdistances($data)
	{
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
		
		$sql = "SELECT 0 AS from_order, 0 AS to_order, 0 AS value";

		foreach ($query->result() as $a)
		{	
			foreach ($query->result() as $b)
			{
				switch($data['method']) {
					case 'euclidian' : 	
						$value = "round(ST_distance(ST_GeometryFromText(''".
									$a->geom_meter."'',900913),ST_GeometryFromText(''".
									$b->geom_meter."'',900913)) / 5.55)";
						break;	
				}	
								
				$sql .= " UNION SELECT ".$a->id.",".$b->id.",".$value;
			}
		}
		
		return $sql;
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
			if($row->pick_up === "t") {
				$geometry = $row->startpoint;
			}
			else {
				$geometry = $row->endpoint;
			}
			
			$feature = array(
				"geometry"    => json_decode($geometry),
				"type"        => "Feature",
				"properties"  => array(
					"id"         => $row->id,
					"order_id"   => $row->order_id,
					"vehicle_id" => $row->vehicle_id,
					"pick_up"    => $row->pick_up,
					"size"       => $row->size,
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

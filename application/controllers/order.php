<?php

class Order extends Controller {

	function Order()
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
	 * Get features
	 */ 
	function find()
	{
		if($this->uri->segment(3) !== FALSE)
		{
			$this->db->where('id', $id = $this->uri->segment(3));
		}
		
		$query = $this->db->get('json_order');
		$this->output->set_output($this->_encode($query));
	}

	/**
	 * Create feature
	 */ 
	function create() {
		$sql = "INSERT INTO crud_order(
						name, size, the_geom,
						pickup, pick_after, pick_before, pick_geom,
						dropoff, drop_after, drop_before, drop_geom ) 
					VALUES( 
						?, ?, ST_MakeLine(ST_GeometryFromText( ? ,4326), ST_GeometryFromText( ? ,4326)), 
						?, ?, ?, ST_GeometryFromText( ? ,4326),
						?, ?, ?, ST_GeometryFromText( ? ,4326) )";
		$data = array(
		   'name'        => $this->input->post('name'),
		   'size'        => $this->input->post('size'),
		   //'the_geom'	 => $this->input->post('wkt_route'),
		   'the_geom1' 	 => $this->input->post('wkt_start'),
		   'the_geom2'	 => $this->input->post('wkt_goal'),
		   'pickup'      => $this->input->post('pickup'),
		   'pick_after'  => $this->input->post('pick_after'),
		   'pick_before' => $this->input->post('pick_before'),
		   'pick_geom'	 => $this->input->post('wkt_start'),
		   'dropoff'     => $this->input->post('dropoff'),
		   'drop_after'  => $this->input->post('drop_after'),
		   'drop_before' => $this->input->post('drop_before'),
		   'drop_geom' 	 => $this->input->post('wkt_goal')
		);
		$this->db->query($sql, $data); 
		$this->output->set_output("{success: true}");
	}

	/**
	 * Update feature
	 */ 
	function update() { 
		$sql = "UPDATE crud_order SET 
					name = ?, size = ? , the_geom = ST_MakeLine(ST_GeometryFromText( ? ,4326), ST_GeometryFromText( ? ,4326)), 
					pickup = ?, pick_after = ?, pick_before = ?, pick_geom = ST_GeometryFromText( ? ,4326),
					dropoff = ?, drop_after = ?, drop_before = ?, drop_geom = ST_GeometryFromText( ? ,4326),
					updated = ? WHERE id = ?";
		$data = array(
		   'name'        => $this->input->post('name'),
		   'size'        => $this->input->post('size'),
		   //'the_geom'	 => $this->input->post('wkt_route'),
		   'the_geom1' 	 => $this->input->post('wkt_start'),
		   'the_geom2'	 => $this->input->post('wkt_goal'),
		   'pickup'      => $this->input->post('pickup'),
		   'pick_after'  => $this->input->post('pick_after'),
		   'pick_before' => $this->input->post('pick_before'),
		   'pick_geom'	 => $this->input->post('wkt_start'),
		   'dropoff'     => $this->input->post('dropoff'),
		   'drop_after'  => $this->input->post('drop_after'),
		   'drop_before' => $this->input->post('drop_before'),
		   'drop_geom' 	 => $this->input->post('wkt_goal'),
		   'update'      => 'now()',
		   'order_id'    => $this->uri->segment(3)
		);
		$this->db->query($sql, $data); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Delete feature
	 */ 
	function delete() { 
		$this->db->where('id', $id = $this->uri->segment(3));
		$this->db->delete('crud_order'); 
		$this->output->set_output("{success: true}");
	}
	
	/**
	 * Build GeoJSON object for "order"
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
				"geometry"    => json_decode($row->geom_route),
				"type"        => "Feature",
				"properties"  => array(
					"order_id"    => $row->id,
					"name"        => $row->name,
					"created"     => $row->created,
					"updated"     => $row->updated,
					"pickup"      => $row->pickup,
					"pick_after"  => $row->pick_after,
					"pick_before" => (-1) * $row->pick_before,
					"dropoff"     => $row->dropoff,
					"drop_after"  => $row->drop_after,
					"drop_before" => (-1) * $row->drop_before,
					"start"       => array(
						"type"        => "Feature",
						"geometry"    => json_decode($row->geom_start),
						"properties"  => array(
							"wkt"     => $row->wkt_start
						),
						"point_id"    => $row->id_start
					),
					"goal"        => array(
						"type"        => "Feature",
						"geometry"    => json_decode($row->geom_goal),
						"properties"  => array(
							"wkt"     => $row->wkt_goal
						),
						"point_id"    => $row->id_goal
					),
					"route"        => array(
						"type"        => "Feature",
						"geometry"    => json_decode($row->geom_route),
						"properties"  => array(
							"wkt"     => $row->wkt_route
						),
						"route_id"    => $row->id
					),
					"size"        => $row->size
				),
				"order_id"        => $row->id
			);
			
			array_push($json["features"], $feature);
		}
		
		return json_encode($json);
	}
}

?>

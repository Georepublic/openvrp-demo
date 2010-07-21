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
			$this->db->where('order_id', $id = $this->uri->segment(3));
		}
		
		$query = $this->db->get('json_orders');
		$this->output->set_output($this->_encode($query));
	}

	/**
	 * Create feature
	 */ 
	function create() {
		$sql = "INSERT INTO orders(name,the_geom,size,account_id,pickup,pick_after,pick_before,dropoff,drop_after,drop_before) 
					VALUES( ?, ST_transform(ST_MakeLine(ST_GeometryFromText( ? ,900913),ST_GeometryFromText( ? ,900913)),4326), ?, ?, ?, ?, ?, ?, ?, ?)";
		$data = array(
		   'name'        => $this->input->post('name'),
		   'geom_start'	 => $this->input->post('wkt_goal'),
		   'geom_goal' 	 => $this->input->post('wkt_start'),
		   'size'        => $this->input->post('size'),
		   'account_id'  => $this->input->post('account_id'),
		   'pickup'      => $this->input->post('pickup'),
		   'pick_after'  => $this->input->post('pick_after'),
		   'pick_before' => $this->input->post('pick_before'),
		   'dropoff'     => $this->input->post('dropoff'),
		   'drop_after'  => $this->input->post('drop_after'),
		   'drop_before' => $this->input->post('drop_before') 
		);
		$this->db->query($sql, $data); 
		$this->output->set_output("{success: true}");
	}

	/**
	 * Update feature
	 */ 
	function update() { 
		$sql = "UPDATE orders SET name = ?, 
					the_geom = ST_transform(ST_MakeLine(ST_GeometryFromText( ? ,900913),ST_GeometryFromText( ? ,900913)),4326), 
					size = ? , account_id = ?, pickup = ?, pick_after = ?, pick_before = ?, dropoff = ?, drop_after = ?, drop_before = ?, 
					updated = ? WHERE order_id = ?";
		$data = array(
		   'name'        => $this->input->post('name'),
		   'geom_start'	 => $this->input->post('wkt_goal'),
		   'geom_goal' 	 => $this->input->post('wkt_start'),
		   'size'        => $this->input->post('size'),
		   'account_id'  => $this->input->post('account_id'),
		   'pickup'      => $this->input->post('pickup'),
		   'pick_after'  => $this->input->post('pick_after'),
		   'pick_before' => $this->input->post('pick_before'),
		   'dropoff'     => $this->input->post('dropoff'),
		   'drop_after'  => $this->input->post('drop_after'),
		   'drop_before' => $this->input->post('drop_before'), 
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
		$this->db->where('order_id', $id = $this->uri->segment(3));
		$this->db->delete('orders'); 
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
				"geometry"    => json_decode($row->geometry),
				"type"        => "Feature",
				"properties"  => array(
					"order_id"    => $row->order_id,
					"account_id"  => $row->account_id,
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
						"order_id"    => $row->order_id
					),
					"goal"        => array(
						"type"        => "Feature",
						"geometry"    => json_decode($row->geom_goal),
						"properties"  => array(
							"wkt"     => $row->wkt_goal
						),
						"order_id"    => $row->order_id
					),
					"size"        => $row->size
				),
				"order_id"        => $row->order_id
			);
			
			array_push($json["features"], $feature);
		}
		
		return json_encode($json);
	}
}

?>

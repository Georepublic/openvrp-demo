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
						'SELECT * FROM darp_orders WHERE depot_id IN (0,".$data['depot_id'].")',
						'SELECT * FROM darp_vehicles WHERE depot_id = ".$data['depot_id']."', 
						'".$distance = $this->_getdistances($data)."'
				) a LEFT JOIN (SELECT * FROM darp_report WHERE depot_id IN (0,".$data['depot_id'].")) AS b ON (a.order_id = b.id);";
		
		//$this->output->set_output($sql);
		
		//$dummy = '{"type":"FeatureCollection","features":[{"geometry":{"type":"Point","coordinates":[15084223.789666,4108309.8325229]},"type":"Feature","properties":{"id":"0","order_id":"0","vehicle_id":"1","pick_up":"t","size":"0","at":"2010-07-31 02:00:00"},"id":"0"},{"geometry":{"type":"Point","coordinates":[15081412.817682,4104996.4609489]},"type":"Feature","properties":{"id":"1","order_id":"4","vehicle_id":"1","pick_up":"t","size":"4","at":"2010-07-31 02:13:03"},"id":"1"},{"geometry":{"type":"Point","coordinates":[15078234.826663,4102716.8506491]},"type":"Feature","properties":{"id":"2","order_id":"4","vehicle_id":"1","pick_up":"f","size":"4","at":"2010-07-31 02:54:28"},"id":"2"},{"geometry":{"type":"Point","coordinates":[15084223.789666,4108309.8325229]},"type":"Feature","properties":{"id":"1","order_id":"0","vehicle_id":"4","pick_up":"t","size":"0","at":"2010-07-31 02:00:00"},"id":"1"},{"geometry":{"type":"Point","coordinates":[15080355.954198,4102229.5645939]},"type":"Feature","properties":{"id":"2","order_id":"5","vehicle_id":"4","pick_up":"t","size":"3","at":"2010-07-31 02:21:38"},"id":"2"},{"geometry":{"type":"Point","coordinates":[15083610.382245,4101738.3326188]},"type":"Feature","properties":{"id":"3","order_id":"6","vehicle_id":"4","pick_up":"t","size":"2","at":"2010-07-31 02:19:49"},"id":"3"},{"geometry":{"type":"Point","coordinates":[15081540.728136,4103318.7922468]},"type":"Feature","properties":{"id":"4","order_id":"7","vehicle_id":"4","pick_up":"t","size":"2","at":"2010-07-31 02:17:01"},"id":"4"},{"geometry":{"type":"Point","coordinates":[15079782.676486,4104780.6504125]},"type":"Feature","properties":{"id":"5","order_id":"6","vehicle_id":"4","pick_up":"f","size":"2","at":"2010-07-31 02:46:54"},"id":"5"},{"geometry":{"type":"Point","coordinates":[15081823.666709,4104728.9313499]},"type":"Feature","properties":{"id":"6","order_id":"5","vehicle_id":"4","pick_up":"f","size":"3","at":"2010-07-31 02:42:49"},"id":"6"},{"geometry":{"type":"Point","coordinates":[15084126.818431,4102773.7663834]},"type":"Feature","properties":{"id":"7","order_id":"3","vehicle_id":"4","pick_up":"t","size":"1","at":"2010-07-31 01:16:22"},"id":"7"},{"geometry":{"type":"Point","coordinates":[15083430.857512,4104304.7781853]},"type":"Feature","properties":{"id":"8","order_id":"3","vehicle_id":"4","pick_up":"f","size":"1","at":"2010-07-31 02:12:16"},"id":"8"},{"geometry":{"type":"Point","coordinates":[15078129.725749,4104627.7763559]},"type":"Feature","properties":{"id":"9","order_id":"8","vehicle_id":"4","pick_up":"t","size":"4","at":"2010-07-31 02:21:23"},"id":"9"},{"geometry":{"type":"Point","coordinates":[15086407.270056,4105153.2809252]},"type":"Feature","properties":{"id":"10","order_id":"2","vehicle_id":"4","pick_up":"t","size":"3","at":"2010-07-31 02:11:32"},"id":"10"},{"geometry":{"type":"Point","coordinates":[15082047.123448,4106701.1307477]},"type":"Feature","properties":{"id":"11","order_id":"1","vehicle_id":"4","pick_up":"t","size":"2","at":"2010-07-31 02:08:08"},"id":"11"},{"geometry":{"type":"Point","coordinates":[15086352.253915,4103419.9472408]},"type":"Feature","properties":{"id":"12","order_id":"1","vehicle_id":"4","pick_up":"f","size":"2","at":"2010-07-31 02:45:53"},"id":"12"},{"geometry":{"type":"Point","coordinates":[15079387.236433,4105990.1423164]},"type":"Feature","properties":{"id":"13","order_id":"8","vehicle_id":"4","pick_up":"f","size":"4","at":"2010-07-31 02:45:58"},"id":"13"},{"geometry":{"type":"Point","coordinates":[15087189.82523,4106276.7811724]},"type":"Feature","properties":{"id":"14","order_id":"2","vehicle_id":"4","pick_up":"f","size":"3","at":"2010-07-31 02:40:40"},"id":"14"},{"geometry":{"type":"Point","coordinates":[15082674.028649,4101308.3743348]},"type":"Feature","properties":{"id":"15","order_id":"7","vehicle_id":"4","pick_up":"f","size":"2","at":"2010-07-31 02:51:24"},"id":"15"}]}';
		//$this->output->set_output($dummy);

		$query = $this->db->query($sql); 
		$this->output->set_output($this->_encode($query));
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

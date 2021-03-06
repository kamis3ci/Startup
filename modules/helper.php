<?php
class SU_Helper {
	function info($table, $id_field, $find_serialize, $id=false, $field='*') {
		$params = array($table, $field);
		if ($id && !is_array($id)) {
			$params[] = array($id_field => $id);
			$method = ($field == '*') ? 'row' : 'field';
		} else {
			$method = ($field == '*') ? 'select' : 'column';
		}
		if (is_array($field)) {
			$method = 'select';
			$params[1] = db::select_clause($field);
		}
		
		if (is_array($id)) {
			$params[] = $id_field . ' IN (' . db::in($id) . ')';
		}
		
		$value = call_user_func_array(array('db', $method), $params);
		
		$find = find_serialize($find_serialize);
		
		if (!is_array($value)) {
			$find($value, $field);
		} elseif ($method == 'column') {
			$value = array_map(function ($item) use ($find, $field) {
				$find($item, $field);
				return $item;
			}, $value);
		} else {		
			array_walk_recursive($value, $find);
		}
		return $value;
	}
	
	// Get/Set meta
	function meta($table, $id_field, $id, $key, $value=null, $delete=false) {
		$result = db::field($table,'meta', array($id_field => $id));
		$meta = unserialize($result);
		if (!is_array($meta)) {
			// something went wrong, initialize to empty array
			$meta = array();
		}
	
		// Get
		if ($value===null) {
			return (isset($meta[$key])) ? $meta[$key] : false;
		} else {
			// Set
			if(!$delete) {
				$meta[$key] = $value;
			} else {
			// unset
				unset($meta[$key]);
			}
			return db::update($table, array('meta' => serialize($meta)), array($id_field => $id));
		}
	}
}
?>
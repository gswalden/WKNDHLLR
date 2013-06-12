<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
	
	public function add_user($user)
	{
		$query = $this->db->get_where('users', ['id' => $user->id]);
		if ($query->num_rows > 0)
		{
			if ($this->db->update('users', ['access_token' => $user->access_token], ['id' => $user->id]));
				return ['bool' => TRUE, 'message' => 'User token updated!'];

			return ['bool' => FALSE, 'message' => 'Database error in ' . __METHOD__ . ': Number: ' . $this->db->_error_number() . '; Message: ' . $this->db->_error_message()];	
		}

		if ($this->db->insert('users', $user))
			return ['bool' => TRUE, 'message' => 'User added!'];

		return ['bool' => FALSE, 'message' => 'Database error in ' . __METHOD__ . ': Number: ' . $this->db->_error_number() . '; Message: ' . $this->db->_error_message()];
	}

	public function get_user($id) 
	{
		return $this->db->get_where('users', ['id' => $id]);
	}
}
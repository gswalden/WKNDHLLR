<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
	
	/**
	 * [add_user description]
	 * @param [type] $user [description]
	 */
	public function add_user($user)
	{
		$query = $this->db->get_where('users', ['id' => $user->id]);
		if ($query->num_rows() > 0)
		{
			if ($this->db->update('users', ['access_token' => $user->access_token], ['id' => $user->id]));
				return ['bool' => TRUE, 'message' => 'User token updated!'];

			return ['bool' => FALSE, 'message' => 'Database error in ' . __METHOD__ . ': Number: ' . $this->db->_error_number() . '; Message: ' . $this->db->_error_message()];	
		}

		if ($this->db->insert('users', $user))
			return ['bool' => TRUE, 'message' => 'User added!'];

		return ['bool' => FALSE, 'message' => 'Database error in ' . __METHOD__ . ': Number: ' . $this->db->_error_number() . '; Message: ' . $this->db->_error_message()];
	}

	/**
	 * [get_user description]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function get_user($id) 
	{
		return $this->db->get_where('users', ['id' => $id]);
	}

	/**
	 * [get_users description]
	 * @param  [type]  $access_level   [description]
	 * @param  boolean $include_higher [description]
	 * @return [type]                  [description]
	 */
	public function get_users($access_level = NULL, $include_higher = TRUE) 
	{
		if (isset($access_level))
		{
			if ($include_higher)
				$this->db->where('access_level >=', $access_level);
			else
				$this->db->where('access_level', $access_level);	
		}
		
		return $this->db->get('users');
	}

	/**
	 * [delete_user description]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function delete_user($id)
	{
		if ($this->db->delete('users', ['id' => $id]))
			return TRUE;
		return FALSE;
	}

	/**
	 * [update_user description]
	 * @param  [type] $id   [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function update_user($id, $data)
	{
		if ($this->db->update('users', $data, ['id' => $id]))
			return TRUE;
		return FALSE;
	}
}
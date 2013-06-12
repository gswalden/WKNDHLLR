<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Photo_model extends CI_Model {

	public function add_photo($id)
	{
		// check for photo in database; if not found, add it
		$query = $this->db->get_where('photos', array('id' => $id));
		if ($query->num_rows == 0)
		{
			$response = $this->instagram_api->getMedia($id);

			if ($response->meta->code == 200)
			{
				$photo = array(
					'id' => $response->data->id,
					'username' => $response->data->user->username,
					'user_id' => $response->data->user->id,
					'low_resolution' => $response->data->images->low_resolution->url,
					'thumbnail' => $response->data->images->thumbnail->url,
					'standard_resolution' => $response->data->images->standard_resolution->url,
					'url' => $response->data->link,
					'added_by' => '387621951'
					);

				// inserts data and returns result
				if ($this->db->insert('photos', $photo))
					return ['bool' => TRUE, 'message' => 'Photo successfully inserted!'];
				
				return ['bool' => FALSE, 'message' => 'Database error in' . __METHOD__ . ': Number: ' . $this->db->_error_number() . '; Message: ' . $this->db->_error_message()];
			}
			else
			{
				$this->load->helper('send_error_mail');
				send_error_mail(__METHOD__, $response->meta->code, $response->meta->error_type, $response->meta->error_message);
				return ['bool' => FALSE, 'message' => 'Error from Instagram API!'];
			}
		}

		return ['bool' => FALSE, 'message' => 'Photo already exists!'];
	}	
}
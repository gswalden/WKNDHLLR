<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Photo_model extends CI_Model {

	public function add_photo($id)
	{
		// check for photo in database; if not found, add it
		$query = $this->db->get_where('photos', ['id' => $id]);
		if ($query->num_rows == 0)
		{
			$response = $this->instagram_api->getMedia($id);

			if ($response->meta->code == 200)
			{
				$photo = $this->_make_photo_array($response);

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

	public function add_suggested_photo($photo)
	{
		$photo = _make_photo_array($photo);

		// inserts data and returns result
		if ($this->db->insert('suggested_photos', $photo))
			return ['bool' => TRUE, 'message' => 'Photo successfully suggested!'];
		
		return ['bool' => FALSE, 'message' => 'Database error in' . __METHOD__ . ': Number: ' . $this->db->_error_number() . '; Message: ' . $this->db->_error_message()];
	}

	public function get_photo($id)
	{
		return $this->db->get_where('photos', ['id' => $id]);
	}

	public function get_photos($limit = 30, $before_date = NULL)
	{
		if (isset($before_id))
			$this->db->where('date_added <', $before_date);
		$this->db->order_by('date_added', 'desc');
		return $this->db->get('photos', $limit);
	}

	public function delete_photo($id)
	{
		if ($this->db->delete('photos', ['id' => $id]))
			return TRUE;
		return FALSE;
	}

	protected function _make_photo_array($photo)
	{
		return ['id' => $photo->data->id,
				'username' => $photo->data->user->username,
				'user_id' => $photo->data->user->id,
				'low_resolution' => $photo->data->images->low_resolution->url,
				'thumbnail' => $photo->data->images->thumbnail->url,
				'standard_resolution' => $photo->data->images->standard_resolution->url,
				'url' => $photo->data->link,
				'added_by' => '387621951'
				];
	}	
}
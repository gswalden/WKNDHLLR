<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Photo_model extends CI_Model {

	/**
	 * Add Photo
	 *
	 * Adds single photo to database.
	 * 
	 * @param string $id Instagram-assigned media_id
	 * @return array     Contains bool and string status message
	 */
	public function add_photo($id)
	{
		// check for photo in database; if not found, add it
		$query = $this->db->get_where('photos', ['id' => $id]);
		if ($query->num_rows() == 0)
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
	/**
	 * Add Suggested Photo
	 *
	 * Adds single photo to suggested_photos table.
	 * 
	 * @param stdClass $photo Response object from Instagram API call
	 * @return array          Contains bool and string status message
	 */
	public function add_suggested_photo($photo)
	{
		$photo = $this->_make_photo_array($photo);

		// inserts data and returns result
		if ($this->db->insert('suggested_photos', $photo))
			return ['bool' => TRUE, 'message' => 'Photo successfully suggested!'];
		
		return ['bool' => FALSE, 'message' => 'Database error in' . __METHOD__ . ': Number: ' . $this->db->_error_number() . '; Message: ' . $this->db->_error_message()];
	}

	/**
	 * Approve Suggested Photo
	 *
	 * Moves a suggested photo from the suggested_photos database to photos database.
	 * 
	 * @param  string $id Instagram-assigned media_id
	 * @return boolean    TRUE on success
	 */
	public function approve_suggested_photo($id)
	{
		$this->db->trans_start();

		$query = $this->db->get_where('suggested_photos', ['id' => $id]);
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			unset($row->created_date, $row->added_by);
			$this->db->insert('photos', $row);
			$this->db->delete('suggested_photos', ['id' => $id]);
		}

		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
			return FALSE;
		return TRUE;
	}

	/**
	 * Remove Suggested Photo
	 *
	 * Deletes a suggested photo from the suggested_photos database.
	 * 
	 * @param  string $id Instagram-assigned media_id
	 * @return boolean    TRUE on success
	 */
	public function remove_suggested_photo($id)
	{
		if ($this->db->delete('suggested_photos', ['id' => $id]))
			return TRUE;
		return FALSE;
	}

	/**
	 * Get Photo
	 *
	 * Returns single photo row from database.
	 * 
	 * @param  string $id Instagram-assigned media_id
	 * @return stdClass   Database result object
	 */
	public function get_photo($id)
	{
		return $this->db->get_where('photos', ['id' => $id]);
	}

	/**
	 * Get Photos
	 * @param  integer $limit       Number of photos to return
	 * @param  string  $before_date Exclude dates including and later
	 * @return stdClass             Database result object
	 */
	public function get_photos($limit = 30, $before_date = NULL)
	{
		if (isset($before_date))
			$this->db->where('date_added <', $before_date);
		$this->db->order_by('date_added', 'desc');
		return $this->db->get('photos', $limit);
	}

	/**
	 * Get Photos by Tags
	 *
	 * Gets a set of photos for each tag from Instagram, removes duplicates, and sorts by date (newest first).
	 * @param  array $tag_list Each element an array include tag name and optional max_id
	 * @return array           Array including array of photos and array of paginiation information for each tag
	 */
	public function get_photos_by_tags($tag_list)
	{
		$photos = [];
		foreach ($tag_list as $tag) 
		{
			$response = $this->instagram_api->tagsRecent($tag['tag'], $tag['max_id']);
			if ($response->meta->code == 200)
			{
				$pagination[$tag['tag']] = $response->pagination->next_max_tag_id;
				$photos = array_merge($photos, $response->data);	
			}
		}
		unset($tag);

		// remove duplicates
		$temp_array = [];
		foreach ($photos as &$photo) 
		{
			$temp_array[$photo->id] = $photo;
			unset($photo);
		}
		$photos = array_values($temp_array);
		unset($temp_array);		

		// sort array by created_time, newest first
		usort($photos, function($a, $b) {
		    return $b->created_time - $a->created_time;
		});
		
		return ['photos' => $photos, 'pagination' => $pagination];
	}

	/**
	 * Delete Photo
	 *
	 * Deletes single photo from database
	 * 
	 * @param  string $id Instagram-assigned media_id
	 * @return boolean    Indicates success or failure
	 */
	public function delete_photo($id)
	{
		if ($this->db->delete('photos', ['id' => $id]))
			return TRUE;
		return FALSE;
	}

	/**
	 * Make Photo Array
	 *
	 * Formats photo information for insertion into database
	 * 
	 * @param  stdClass $photo Object from Instagram API call
	 * @return array           Properly formatted for insertion into photos database
	 */
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
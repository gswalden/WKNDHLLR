<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tastemaker extends CI_Controller {

	protected $tags = array('rave',
							'vip',
							'partybus',
							'trap');

	public function index()
	{
		$tags = array('rave',
							'vip',
							'partybus',
							'trap');
		// Check if user is an admin
		//if ($this->session->userdata('access_level') < 2)
		//	redirect('/');

		// Check response
		// $data->meta->code;

		$data['login_url'] = $this->instagram_api->instagramLogin();
		// Set the instagram library access token variable
 		$this->instagram_api->access_token = $this->session->userdata('instagram-token');
		
		$data['photo_array'] = array();
		// $m = 0;
		foreach ($tags as $tag) 
		{
			$response = $this->instagram_api->tagsRecent($tag);
			//var_dump($response);
			// if ($m < 2)
			// {
			// 	var_dump($response);
			// 	$m++;
			// }
			$data['photo_array'] = array_merge($data['photo_array'], $response->data);
		}
		unset($tag);
		
		// while (count($data['photo_array']) <= 50) 
		// {
		// 	$response = $this->getPhotoArray($response->pagination->next_url);
		// 	$data['photo_array'] = array_merge($data['photo_array'], $response->data);			
		// }

		// var_dump($data['photo_array']);

		// remove duplicates
		$temp_array = array();
		foreach ($data['photo_array'] as &$photo) 
		{
			$temp_array[$photo->id] = $photo;
			unset($photo);
		}
		$data['photo_array'] = array_values($temp_array);
		unset($temp_array);		

		// sort array by created_time, newest first
		usort($data['photo_array'], function($a, $b) {
		    return $b->created_time - $a->created_time;
		});
		
		$this->load->view('basic', $data);
	}

	public function add($id)
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
				$this->db->insert('photos', $photo);
			}	
		}
		
		redirect('/tastemaker');
	}

	protected function getPhotoArray($url)
	{
		return $this->instagram_api->__apiCall($url);
	}
}
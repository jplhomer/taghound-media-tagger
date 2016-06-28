<?php

use Taghound_Media_Tagger\Clarifai\API\Client;

class TestBatchTagger extends WP_UnitTestCase {
	protected $info = array(
		"status_code" => "OK",
		"status_msg" => "All images in request have completed successfully. ",
		"results" => array(
			"max_image_size" => 100000,
			"default_language" => "en",
			"max_video_size" => 100000,
			"max_image_bytes" => 10485760,
			"min_image_size" => 1,
			"default_model" => "general-v1.3",
			"max_video_bytes" => 104857600,
			"max_video_duration" => 1800,
			"max_batch_size" => 128,
			"max_video_batch_size" => 1,
			"min_video_size" => 1,
			"api_version" => 0.1
		)
	);

	public function setUp() {

	}
}

<?php

$benyon_ccs_video_data = array(
		array(
			'key' => 'field_5a9816e456d46',
			'label' => 'Poster Frame',
			'name' => 'poster_frame',
			'type' => 'image',
			'instructions' => 'This is the image that will appear as a static freeze frame behind the play button.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'id',
			'preview_size' => 'thumbnail',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
		),
		array(
			'key' => 'field_5a980f30f0682',
			'label' => 'Youtube Video ID',
			'name' => 'youtube_video_id',
			'type' => 'text',
			'instructions' => 'A YouTube video ID. This is the characters in the video url that appear after "https://www.youtube.com/watch?v=" or after "https://youtu.be/"
<br>
<br>
In both of these examples the video ID is: <b>s4D42vMUSIM</b>
<br>
- https://www.youtube.com/watch?v=s4D42vMUSIM\\n
<br>
- https://youtu.be/s4D42vMUSIM',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 's4D42vMUSIM',
			'prepend' => 'https://www.youtube.com/watch?v=',
			'append' => '',
			'maxlength' => '',
		)
	);

<?php

/***************************************************************************
Sample Implementation of Claude 3 Vision API 
It will get an image as base64 encoded string from the front-end form
and calls the Claude 3 Vision API to identify the ingredients inside the image.
*****************************************************************************/

// Main function for calling Claude 3 Vision API
function callClaude($img_data) {
    $url = 'https://api.anthropic.com/v1/messages';
    $apiKey = "XXXXXXXX";

    $messages = [
        [
            'role'    => 'user',
            'content' => [
                [
                    'type' => 'image', // Your offering, in image form.
                    'source' => [
                        'type' => 'base64',
                        'media_type' => 'image/jpeg',
                        'data' => $img_data
                    ]
                ],
                [
                    'type' => 'text', // Your request, in words.
                    'text' => "Attached is an image of food ingredients. Give me a comma-separated list of ingredient titles. In your list, do not include or attach other information, such as locations, brands, containers, packaging, shapes, situations, positions, etc. Only the ingredient titles."
                ],
            ],
        ]
    ];

    $postData = [
        'model' => 'claude-3-opus-20240229',
        'max_tokens' => 2048,
        'messages' => $messages
    ];

    $jsonData = json_encode($postData);

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json'
        ],
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }
    $commaSeparatedIngredients = $responseData['content'][0]['text'];
    if (empty($commaSeparatedIngredients)) {
        return [];
    }
    $commaSeparatedIngredients = str_replace('.', '', $commaSeparatedIngredients);
    $commaSeparatedIngredients = strtolower($commaSeparatedIngredients); 
    $commaSeparatedIngredients = preg_replace('/\s*,\s*/', ',', $commaSeparatedIngredients);
    $ingredientsArray = explode(',', $commaSeparatedIngredients);
    $ingredientsArray = array_unique($ingredientsArray);
    return $ingredientsArray;
}








if (!empty($_POST['img_base64'])) {
	$Base64_img = $_POST['img_base64'];  // Base64 String of the uploaded image.
	$ingredient_array = callClaude($Base64_img); // Example output => ["egg", "meat", "apple", "banana"]

	if (!empty($ingredient_array)) {
		foreach($ingredient_array as $ingredient) {
			echo $ingredient;
		}
	}
}


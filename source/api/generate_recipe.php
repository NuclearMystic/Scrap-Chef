<?php
require_once "config.php";

header("Content-Type: application/json");

// Read input JSON
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data["ingredients"]) || !is_array($data["ingredients"])) {
    echo json_encode(["error" => "Invalid input. Expected an array of ingredients."]);
    exit;
}

// Convert ingredients into a formatted prompt
$ingredient_list = implode(", ", $data["ingredients"]);
$prompt = "Create a professional chef-level recipe using the following scrap ingredients: $ingredient_list. 
Format the response as: 

Recipe Name: [Recipe Title]

Ingredients:
- [Ingredient 1]
- [Ingredient 2]

Instructions:
1. [Step 1]
2. [Step 2]";

// OpenAI API request
function callOpenAI($prompt) {
    $url = "https://api.openai.com/v1/chat/completions";
    $data = [
        "model" => "gpt-4-turbo",
        "messages" => [
            ["role" => "system", "content" => "You are a professional chef. Provide structured recipes."],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 300,
        "temperature" => 0.7
    ];

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . OPENAI_API_KEY
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Call OpenAI API
$response = callOpenAI($prompt);

if (isset($response["choices"][0]["message"]["content"])) {
    $recipe_text = $response["choices"][0]["message"]["content"];

    // Extract recipe name, ingredients, and instructions
    preg_match("/Recipe Name:\s*(.*?)\n\nIngredients:\n(.*?)\n\nInstructions:\n(.*)/s", $recipe_text, $matches);

    if (!empty($matches)) {
        // Split instructions while ignoring the first empty match
        $instructions = preg_split('/\d+\.\s*/', trim($matches[3]));
        $instructions = array_filter($instructions); // Remove empty values
        $instructions = array_values($instructions); // Reset array keys

        $recipe = [
            "name" => trim($matches[1]),
            "ingredients" => array_filter(array_map('trim', explode("\n", trim($matches[2])))),
            "instructions" => $instructions
        ];

        echo json_encode($recipe, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["error" => "Failed to parse recipe"]);
    }
} else {
    echo json_encode(["error" => "Failed to generate recipe"]);
}



?>

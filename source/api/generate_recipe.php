<?php
require_once "config.php";

header("Content-Type: application/json");

// Read input JSON
$data = json_decode(file_get_contents("php://input"), true);

// Debugging output
error_log("Received JSON: " . print_r($data, true));

// Validate input data
if (!isset($data["ingredients"]) || !is_array($data["ingredients"])) {
    echo json_encode(["error" => "Invalid input. Expected an array of ingredient objects."]);
    exit;
}

// Ensure each ingredient has id, name, and quantity (ignore price)
$valid_ingredients = [];
foreach ($data["ingredients"] as $ing) {
    if (isset($ing["id"], $ing["name"], $ing["quantity"])) {
        $valid_ingredients[] = $ing;
    }
}

// If no valid ingredients found, return an error
if (count($valid_ingredients) === 0) {
    echo json_encode(["error" => "No valid ingredients provided."]);
    exit;
}

// Convert ingredients into a formatted prompt
$ingredient_list = implode(", ", array_map(function ($ing) {
    return $ing["quantity"] . " of " . $ing["name"] . " (ID: " . $ing["id"] . ")";
}, $valid_ingredients));

error_log("Formatted ingredient list: " . $ingredient_list); // Debugging output

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

    $api_key = getenv("OPENAI_API_KEY"); // Get API key from environment variables

    if (!$api_key) {
        error_log("⚠️ OpenAI API key is missing!");
        echo json_encode(["error" => "OpenAI API key is missing."]);
        exit;
    }

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer " . $api_key
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("OpenAI API Error: HTTP $http_code - Response: " . print_r($response, true));
        return ["error" => "OpenAI API request failed."];
    }

    return json_decode($response, true);
}

// Call OpenAI API
$response = callOpenAI($prompt);
error_log("OpenAI Response: " . print_r($response, true)); // Debugging output

if (isset($response["choices"][0]["message"]["content"])) {
    $recipe_text = $response["choices"][0]["message"]["content"];

    preg_match("/Recipe Name:\s*(.*?)\n\nIngredients:\n(.*?)\n\nInstructions:\n(.*)/s", $recipe_text, $matches);

    if (!empty($matches)) {
        $instructions = preg_split('/\d+\.\s*/', trim($matches[3]));
        $instructions = array_filter($instructions);
        $instructions = array_values($instructions);

        $recipe = [
            "name" => trim($matches[1]),
            "ingredients" => array_map('trim', explode("\n", trim($matches[2]))),
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

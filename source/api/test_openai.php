<?php
require_once "config.php";

function callOpenAI($prompt) {
    $url = "https://api.openai.com/v1/chat/completions";
    $data = [
        "model" => "gpt-4-turbo",
        "messages" => [
            ["role" => "system", "content" => "You are a professional chef. Provide structured recipes with clear ingredient lists and step-by-step instructions. Format response as:\n\nRecipe Name: [Recipe Title]\n\nIngredients:\n- [Ingredient 1]\n- [Ingredient 2]\n\nInstructions:\n1. [Step 1]\n2. [Step 2]\n"],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 300,
        "temperature" => 0.7
    ];

    $headers = [
        "Content-Type: application/json",
        "Authorization: " . "Bearer " . OPENAI_API_KEY
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (isset($decoded_response["choices"][0]["message"]["content"])) {
        $recipe_text = $decoded_response["choices"][0]["message"]["content"];

        // Extract the recipe name, ingredients, and instructions using more flexible parsing
        preg_match("/Recipe Name:\s*(.*?)\n\nIngredients:\n(.*?)\n\nInstructions:\n(.*)/s", $recipe_text, $matches);

        if (!empty($matches)) {
            $recipe = [
                "name" => trim($matches[1]),
                "ingredients" => array_filter(array_map('trim', explode("\n", trim($matches[2])))),
                "instructions" => array_filter(array_map('trim', explode("\n", trim($matches[3]))))
            ];
            echo json_encode($recipe, JSON_PRETTY_PRINT);
        } else {
            echo json_encode(["error" => "Failed to parse recipe"]);
        }
    } else {
        echo json_encode(["error" => "Failed to generate recipe"]);
    }
}

// Test with a sample prompt
callOpenAI("Create a simple pasta recipe using tomatoes and garlic.");
?>

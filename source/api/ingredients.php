<?php
header("Content-Type: application/json");
require_once "db_connect.php"; // Ensure this file contains the DB connection

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['id'])) {
            getIngredient($_GET['id']);
        } else {
            getAllIngredients();
        }
        break;

    case "POST":
        addIngredient();
        break;

    case "PUT":
        updateIngredient();
        break;

    case "DELETE":
        deleteIngredient();
        break;

    default:
        echo json_encode(["error" => "Invalid request"]);
}

function getAllIngredients() {
    global $conn;
    $sql = "SELECT * FROM Ingredients";
    $result = $conn->query($sql);
    $ingredients = [];

    while ($row = $result->fetch_assoc()) {
        $ingredients[] = $row;
    }
    
    echo json_encode($ingredients);
}

function getIngredient($id) {
    global $conn;
    $sql = "SELECT * FROM Ingredients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Ingredient not found"]);
    }
}

function addIngredient() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['name'], $data['current_price'], $data['quantity'])) {
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $sql = "INSERT INTO Ingredients (name, current_price, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $data['name'], $data['current_price'], $data['quantity']);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Ingredient added successfully"]);
    } else {
        echo json_encode(["error" => "Failed to add ingredient"]);
    }
}

function updateIngredient() {
    global $conn;
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['id'], $data['name'], $data['current_price'], $data['quantity'])) {
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $sql = "UPDATE Ingredients SET name = ?, current_price = ?, quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdii", $data['name'], $data['current_price'], $data['quantity'], $data['id']);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Ingredient updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update ingredient"]);
    }
}

function deleteIngredient() {
    global $conn;
    parse_str(file_get_contents("php://input"), $data);

    if (!isset($data['id'])) {
        echo json_encode(["error" => "Missing ingredient ID"]);
        return;
    }

    $sql = "DELETE FROM Ingredients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Ingredient deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete ingredient"]);
    }
}
?>

<?php
header("Content-Type: application/json");
require_once "db_connect.php"; // Ensure DB connection is included

$method = $_SERVER['REQUEST_METHOD'];
error_log("Received method: " . $_SERVER['REQUEST_METHOD']); // Log request type

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
        echo json_encode(["error" => "Invalid request method"]);
        exit;
}

// Function to fetch all ingredients
function getAllIngredients() {
    global $conn;
    $sql = "SELECT id, name, current_price, quantity FROM Ingredients";
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["error" => "Database query failed: " . $conn->error]);
        exit;
    }

    $ingredients = [];
    while ($row = $result->fetch_assoc()) {
        $ingredients[] = $row;
    }

    echo json_encode($ingredients);
}

// Function to fetch a single ingredient by ID
function getIngredient($id) {
    global $conn;

    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        echo json_encode(["error" => "Invalid ingredient ID"]);
        exit;
    }

    $sql = "SELECT id, name, current_price, quantity FROM Ingredients WHERE id = ?";
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

// Function to add a new ingredient
function addIngredient() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['name'], $data['current_price'], $data['quantity'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $name = trim($data['name']);
    $current_price = (float) $data['current_price'];
    $quantity = (int) $data['quantity'];

    if (empty($name) || $current_price < 0 || $quantity < 0) {
        echo json_encode(["error" => "Invalid data provided"]);
        exit;
    }

    // Check if the ingredient already exists
    $checkSql = "SELECT id, quantity FROM Ingredients WHERE name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkRow = $checkResult->fetch_assoc()) {
        // Ingredient exists, update quantity
        $newQuantity = $checkRow['quantity'] + $quantity;
        $updateSql = "UPDATE Ingredients SET quantity = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $newQuantity, $checkRow['id']);

        if ($updateStmt->execute()) {
            echo json_encode(["message" => "Ingredient quantity updated", "id" => $checkRow['id'], "new_quantity" => $newQuantity]);
        } else {
            echo json_encode(["error" => "Failed to update ingredient quantity"]);
        }
    } else {
        // Ingredient does not exist, insert new ingredient
        $insertSql = "INSERT INTO Ingredients (name, current_price, quantity) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sdi", $name, $current_price, $quantity);

        if ($insertStmt->execute()) {
            echo json_encode(["message" => "Ingredient added successfully", "id" => $insertStmt->insert_id]);
        } else {
            echo json_encode(["error" => "Failed to add ingredient: " . $insertStmt->error]);
        }
    }
}


// Function to update an ingredient
function updateIngredient() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id'], $data['name'], $data['current_price'], $data['quantity'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    $name = trim($data['name']);
    $current_price = filter_var($data['current_price'], FILTER_VALIDATE_FLOAT);
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);

    if (!$id || !$name || $current_price === false || $quantity === false || $current_price < 0 || $quantity < 0) {
        echo json_encode(["error" => "Invalid data provided"]);
        exit;
    }

    $sql = "UPDATE Ingredients SET name = ?, current_price = ?, quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdii", $name, $current_price, $quantity, $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Ingredient updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update ingredient: " . $stmt->error]);
    }
}

// Function to delete one or multiple ingredients
function deleteIngredient() {
    global $conn;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['ids']) || !is_array($data['ids'])) {
        echo json_encode(["error" => "Missing ingredient ID(s)"]);
        exit;
    }

    // Validate all IDs
    $ids = array_filter($data['ids'], function ($id) {
        return filter_var($id, FILTER_VALIDATE_INT);
    });

    if (empty($ids)) {
        echo json_encode(["error" => "Invalid ingredient ID(s)"]);
        exit;
    }

    $idList = implode(",", $ids); // Convert array to comma-separated string

    $sql = "DELETE FROM Ingredients WHERE id IN ($idList)";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Ingredients deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete ingredients: " . $conn->error]);
    }
}
?>

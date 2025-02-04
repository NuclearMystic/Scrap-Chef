document.addEventListener("DOMContentLoaded", () => {
    loadIngredients(); // From ingredients.js

    document.getElementById("generate-recipe").addEventListener("click", () => {
        const selectedIngredients = getSelectedIngredients();

        if (selectedIngredients.length === 0) {
            alert("Please add at least one ingredient to generate a recipe.");
            return;
        }

        console.log("Sending to API:", JSON.stringify({ ingredients: selectedIngredients }));

        fetch("api/generate_recipe.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ingredients: selectedIngredients })
        })
        .then(response => response.json())
        .then(data => {
            console.log("API Response:", data);
            const recipeResult = document.getElementById("recipe-result");
            if (data.error) {
                recipeResult.innerHTML = `<p class="error">${data.error}</p>`;
            } else {
                recipeResult.innerHTML = `
                    <h3>${data.name}</h3>
                    <h4>Ingredients:</h4>
                    <ul>
                        ${data.ingredients.map(i => `<li>${i.name} - ${i.quantity}</li>`).join("")}
                    </ul>
                    <h4>Instructions:</h4>
                    <ol>
                        ${data.instructions.map(i => `<li>${i}</li>`).join("")}
                    </ol>
                `;
            }
        })
        .catch(error => console.error("Error generating recipe:", error));
    });

    document.getElementById("add-ingredient-form").addEventListener("submit", addIngredient);
});

// ✅ Get all ingredients from the "Selected Ingredients" table (NO CHECKBOXES)
function getSelectedIngredients() {
    const selectedRows = document.querySelectorAll("#selected-ingredients tr");

    return Array.from(selectedRows).map(row => ({
        id: row.dataset.ingredientId, // Ensure the ID is properly stored
        name: row.querySelector("td:nth-child(1)")?.textContent.trim(),
        quantity: row.querySelector("td:nth-child(2)")?.textContent.trim()
    })).filter(ingredient => ingredient.id && ingredient.name && ingredient.quantity);
}

// ✅ Function to add a new ingredient
function addIngredient(event) {
    event.preventDefault();

    const name = document.getElementById("ingredient-name").value.trim();
    const quantity = document.getElementById("ingredient-quantity").value;
    const price = document.getElementById("ingredient-price").value;

    if (!name || quantity <= 0 || price < 0) {
        alert("Please enter valid ingredient details.");
        return;
    }

    fetch("api/ingredients.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, quantity, current_price: price })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            loadIngredients(); // Refresh list after adding
            document.getElementById("add-ingredient-form").reset(); // Clear form
        }
    })
    .catch(error => {
        console.error("Error adding ingredient:", error);
        error.text().then(text => console.error("Server response:", text)); // Log full response
    });
}

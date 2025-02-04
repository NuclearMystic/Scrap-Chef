function generateRecipe() {
    const button = document.getElementById("generate-recipe");
    const buttonText = button.querySelector(".button-text");
    const spinner = button.querySelector(".spinner");

    // Show loading state
    buttonText.style.display = "none";
    spinner.style.display = "inline-block";
    button.classList.add("loading");
    button.disabled = true;

    // Collect all ingredients from the "Selected Ingredients" table
    const selectedRows = document.querySelectorAll("#selected-ingredients tr");
    const ingredients = [];

    selectedRows.forEach(row => {
        const id = row.dataset.ingredientId; // Ensure ID is included
        const nameElement = row.querySelector("td:nth-child(1)");
        const quantityElement = row.querySelector("td:nth-child(2)");

        const name = nameElement ? nameElement.textContent.trim() : "";
        const quantity = quantityElement ? quantityElement.textContent.trim() : "";

        if (id && name && quantity) { 
            ingredients.push({ id, name, quantity }); // Do NOT include price
        }
    });

    // Ensure at least one ingredient is present
    if (ingredients.length === 0) {
        alert("Please add at least one ingredient before generating a recipe.");
        buttonText.style.display = "inline";
        spinner.style.display = "none";
        button.classList.remove("loading");
        button.disabled = false;
        return;
    }

    console.log("Sending to API:", JSON.stringify({ ingredients })); // Debugging output

    fetch("api/generate_recipe.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ingredients }) // Send full ingredient data
    })
    .then(response => response.json())
    .then(data => {
        console.log("API Response:", data); // Debugging output
        const recipeResult = document.getElementById("recipe-result");

        if (data.error) {
            recipeResult.innerHTML = `<p class="error">${data.error}</p>`;
        } else {
            // Ensure ingredients are displayed correctly
            const formattedIngredients = data.ingredients.map(ing => 
                typeof ing === "object" 
                ? `<li>${ing.name} - ${ing.quantity}</li>` 
                : `<li>${ing}</li>`
            ).join("");

            recipeResult.innerHTML = `
                <h3>${data.name}</h3>
                <h4>Ingredients:</h4>
                <ul>${formattedIngredients}</ul>
                <h4>Instructions:</h4>
                <ol>${data.instructions.map(i => `<li>${i}</li>`).join("")}</ol>
            `;
        }
    })
    .catch(error => console.error("Error generating recipe:", error))
    .finally(() => {
        buttonText.style.display = "inline";
        spinner.style.display = "none";
        button.classList.remove("loading");
        button.disabled = false;
    });
}

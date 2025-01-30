document.addEventListener("DOMContentLoaded", () => {
    loadIngredients();

    document.getElementById("generate-recipe").addEventListener("click", () => {
        const selectedIngredients = getSelectedIngredients();
        if (selectedIngredients.length === 0) {
            alert("Please select at least one ingredient.");
            return;
        }
        generateRecipe(selectedIngredients);
    });
});

// Fetch available ingredients from the API
function loadIngredients() {
    fetch("api/ingredients.php")
        .then(response => response.json())
        .then(data => {
            console.log("Fetched ingredients:", data); // Debugging log
            const ingredientList = document.getElementById("ingredient-list");
            ingredientList.innerHTML = "";
            if (!Array.isArray(data) || data.length === 0) {
                ingredientList.innerHTML = "<p>No ingredients found.</p>";
                return;
            }
            data.forEach(ingredient => {
                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.value = ingredient.name;
                checkbox.id = "ingredient_" + ingredient.id;
                
                const label = document.createElement("label");
                label.htmlFor = checkbox.id;
                label.textContent = ingredient.name;

                const div = document.createElement("div");
                div.classList.add("ingredient-item");
                div.appendChild(checkbox);
                div.appendChild(label);

                ingredientList.appendChild(div);
            });
        })
        .catch(error => {
            console.error("Error loading ingredients:", error);
            document.getElementById("ingredient-list").innerHTML = "<p>Error fetching ingredients.</p>";
        });
}


// Get selected ingredients
function getSelectedIngredients() {
    const checkboxes = document.querySelectorAll("#ingredient-list input:checked");
    return Array.from(checkboxes).map(cb => cb.value);
}

// Send selected ingredients to API and display recipe
function generateRecipe(ingredients) {
    const button = document.getElementById("generate-recipe");
    const buttonText = button.querySelector(".button-text");
    const spinner = button.querySelector(".spinner");

    // Show loading state
    buttonText.style.display = "none";
    spinner.style.display = "inline-block";
    button.classList.add("loading");
    button.disabled = true;

    fetch("api/generate_recipe.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ingredients })
    })
    .then(response => response.json())
    .then(data => {
        const recipeResult = document.getElementById("recipe-result");
        if (data.error) {
            recipeResult.innerHTML = `<p class="error">${data.error}</p>`;
        } else {
            const cleanedInstructions = data.instructions.map(step => step.replace(/\n/g, '').trim());

            recipeResult.innerHTML = `
                <h3>${data.name}</h3>
                <h4>Ingredients:</h4>
                <ul>${data.ingredients.map(i => `<li>${i}</li>`).join("")}</ul>
                <h4>Instructions:</h4>
                <ol>${cleanedInstructions.map(i => `<li>${i}</li>`).join("")}</ol>
            `;
        }
    })
    .catch(error => console.error("Error generating recipe:", error))
    .finally(() => {
        // Restore button text after recipe loads
        buttonText.style.display = "inline";
        spinner.style.display = "none";
        button.classList.remove("loading");
        button.disabled = false;
    });
}




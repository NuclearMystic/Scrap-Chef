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
                const row = document.createElement("tr");

                row.innerHTML = `
                    <td>
                        <input type="checkbox" 
                               value="${ingredient.id}" 
                               data-name="${ingredient.name}" 
                               data-quantity="${ingredient.quantity}">
                    </td>
                    <td>${ingredient.name}</td>
                    <td>${ingredient.quantity}</td>
                    <td>$${ingredient.current_price}</td>
                `;

                ingredientList.appendChild(row);
            });
        })
        .catch(error => {
            console.error("Error loading ingredients:", error);
            document.getElementById("ingredient-list").innerHTML = "<p>Error fetching ingredients.</p>";
        });
}


// Add selected ingredients to the recipe section
document.getElementById("add-selected-ingredients").addEventListener("click", () => {
    const selectedIngredientsList = document.getElementById("selected-ingredients");
    const selectedCheckboxes = document.querySelectorAll("#ingredient-list input:checked");

    if (!selectedIngredientsList) {
        console.error("Error: Could not find tbody inside #selected-ingredients");
        return;
    }

    selectedCheckboxes.forEach(cb => {
        const row = cb.closest("tr");

        const id = cb.value;  // Ingredient ID (stored in checkbox value)
        const name = row.querySelector("td:nth-child(2)")?.textContent.trim(); // Get ingredient name
        const quantity = row.querySelector("td:nth-child(3)")?.textContent.trim(); // Get quantity

        if (!id || !name || !quantity) {
            console.error("Missing ingredient details:", { id, name, quantity });
            return;
        }

        // Check if ingredient already exists in the list
        if ([...selectedIngredientsList.children].some(tr => tr.dataset.ingredientId === id)) {
            return; // Avoid adding duplicates
        }

        const newRow = document.createElement("tr");
        newRow.dataset.ingredientId = id; // Store the ID for reference

        newRow.innerHTML = `
            <td>${name}</td>
            <td>${quantity}</td>
        `;

        selectedIngredientsList.appendChild(newRow);
    });

    // Uncheck checkboxes after adding
    selectedCheckboxes.forEach(cb => cb.checked = false);
});


// ✅ Remove selected ingredients from the Available Ingredients List (By ID)
document.getElementById("remove-selected-ingredients-list").addEventListener("click", () => {
    const selectedCheckboxes = document.querySelectorAll("#ingredient-list input:checked");

    if (selectedCheckboxes.length === 0) {
        alert("No ingredients selected to remove.");
        return;
    }

    if (!confirm("Are you sure you want to remove the selected ingredients?")) return;

    const ingredientIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.ingredientId); // Use ID

    fetch("api/ingredients.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ids: ingredientIds }) // Send IDs to remove
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            loadIngredients(); // Refresh list after removal
        }
    })
    .catch(error => console.error("Error removing ingredient:", error));
});

// ✅ Remove selected ingredients from the recipe section (By ID)
document.getElementById("remove-selected-ingredients").addEventListener("click", () => {
    const selectedCheckboxes = document.querySelectorAll("#selected-ingredients input:checked");

    selectedCheckboxes.forEach(cb => {
        cb.closest("tr").remove();
    });
});

// ✅ Remove an ingredient from the available ingredients list (By ID)
function removeIngredient(id) {
    if (!confirm("Are you sure you want to remove this ingredient?")) return;

    fetch(`api/ingredients.php?id=${id}`, { method: "DELETE" })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            loadIngredients(); // Refresh list
        }
    })
    .catch(error => console.error("Error removing ingredient:", error));
}

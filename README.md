# Scrap-Chef
 Waste reduction tool for BOH staff.

 1. Introduction

1.1 Overview
Scrap Chef is a web-based tool designed to help chefs create recipes using ingredients that would otherwise be considered waste. By leveraging AI-powered recipe generation, Scrap Chef aims to minimize food waste in restaurant kitchens while optimizing ingredient utilization.

1.2 Purpose
The purpose of Scrap Chef is to enable back-of-house kitchen staff to input scrap ingredients and generate creative, usable recipes. The system will integrate with a restaurant's ingredient database to provide relevant suggestions based on available stock.

1.3 Scope
Scrap Chef will provide an easy-to-use interface where chefs can:

Input scrap ingredients into the system.

Generate recipes using OpenAI's API.

Utilize existing restaurant inventory to enhance recipe relevance.

Store and retrieve scrap ingredient data via a database.

2. Functional Requirements

The system shall allow users to input scrap ingredients.

The system shall connect with a database to store and retrieve ingredient data.

The system shall generate recipes using OpenAI’s API based on user-selected scrap ingredients.

The system shall display generated recipes to the user within a reasonable response time.

The system shall provide an option to refine or regenerate recipes if needed.

3. Non-Functional Requirements

Performance: Recipe generation should take no longer than 5 seconds for normal-sized databases.

Usability: The UI should be intuitive and accessible across multiple devices.

Security: API calls should be securely managed to prevent misuse.

4. System Architecture (Basic Overview)

Frontend: Web-based UI for user interactions (HTML, CSS, JavaScript).

Backend: Handles API requests, database queries, and business logic (PHP).

Database: Stores restaurant inventory and scrap ingredient data (SQL, managed with XAMPP/MyPHPAdmin).

API Integration: OpenAI API for AI-driven recipe generation.

5. Constraints & Limitations

API usage costs and rate limits may impact how often recipes can be generated.

Larger restaurant databases may lead to longer processing times.

Requires connectivity to the network where the restaurant's database is stored (no offline mode planned at this stage).

6. Assumptions & Dependencies

OpenAI’s API will remain available and functional for recipe generation.

The system will be deployed in a restaurant environment where staff have access to a web browser.

The ingredient database will be properly maintained and updated by restaurant staff.

7. Technology Stack

Frontend: HTML, CSS, JavaScript.

Backend: PHP for server-side logic.

Database: SQL database (MySQL) managed via XAMPP and MyPHPAdmin.

API Integration: OpenAI API for recipe generation.

Hosting Environment: Local restaurant networks for database hosting, with the web app potentially deployed on platforms like Netlify or GitHub Pages.

Version Control: GitHub for source code management.

Development Tools: Visual Studio Code and browser-based debugging tools.

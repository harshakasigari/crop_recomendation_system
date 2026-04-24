<?php
session_start();
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'csp';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pesticide data
$pesticides = [
    "Rice" => [
        "Vegetative" => ["Chlorantraniliprole", "Thiamethoxam"],
        "Reproductive" => ["Tricyclazole", "Carbendazim"],
        "Maturity" => ["Azoxystrobin", "Propiconazole"]
    ],
    "Wheat" => [
        "Vegetative" => ["Lambda-cyhalothrin", "Imidacloprid"],
        "Reproductive" => ["Tebuconazole", "Propiconazole"],
        "Maturity" => ["Chlorpyriphos", "Mancozeb"]
    ],
    // Add more crops as needed
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop = htmlspecialchars($_POST['crop']);
    $growth_stage = htmlspecialchars($_POST['growth_stage']);
    
    // Store in database
    $recommended_pesticides = isset($pesticides[$crop][$growth_stage]) ? 
        implode(", ", $pesticides[$crop][$growth_stage]) : "No recommendations available";
    
    $stmt = $conn->prepare("INSERT INTO pesticide_recommendations (farmer_name, crop, growth_stage, recommended_pesticides) VALUES (?, ?, ?, ?)");
    $farmer_name = $_SESSION['farmer_name'] ?? 'Unknown';
    $stmt->bind_param("ssss", $farmer_name, $crop, $growth_stage, $recommended_pesticides);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pesticide Recommendation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2a5298;
            --secondary-color: #1a3e72;
            --accent-color: #4CAF50;
            --light-bg: #f8f9fa;
            --card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding-bottom: 2rem;
        }
        
        .container {
            max-width: 600px;
            animation: fadeIn 0.5s ease-out;
        }
        
        .form-card {
            background: white;
            box-shadow: var(--card-shadow);
            border-radius: 16px;
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .app-title {
            font-weight: 700;
            margin-bottom: 0.5em;
            color: var(--primary-color);
            font-size: 2rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: var(--accent-color);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card form-card p-4">
        <div class="text-center pb-4">
            <h2 class="app-title">🌾 Pesticide Recommendation</h2>
            <p class="text-muted">Get pesticide recommendations based on crop and growth stage</p>
        </div>
        
        <form method="POST" action="pesticide_result.php">
            <div class="mb-3">
                <label class="form-label">Crop Name</label>
                <input type="text" name="crop" class="form-control" placeholder="Enter crop name (e.g., Rice, Wheat)" required>
                <small class="text-muted">Note: Currently we have recommendations for Rice and Wheat only</small>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Growth Stage</label>
                <select name="growth_stage" class="form-select" required>
                    <option value="" disabled selected>Select growth stage</option>
                    <option value="Vegetative">Vegetative</option>
                    <option value="Reproductive">Reproductive</option>
                    <option value="Maturity">Maturity</option>
                </select>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-success">Get Recommendations</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
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
    
    $recommendations = $pesticides[$crop][$growth_stage] ?? ["No recommendations available"];
    
    // Store in database (without farmer name)
    $stmt = $conn->prepare("INSERT INTO pesticide_recommendations (crop, growth_stage, recommended_pesticides) VALUES (?, ?, ?)");
    $pesticide_str = implode(", ", $recommendations);
    $stmt->bind_param("sss", $crop, $growth_stage, $pesticide_str);
    $stmt->execute();
    $stmt->close();
} else {
    header("Location: pesticide.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pesticide Recommendations</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2a5298;
            --accent-color: #4CAF50;
            --light-bg: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .result-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .result-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .pesticide-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        .pesticide-list li {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background-color: var(--light-bg);
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="result-card">
        <h2 class="result-title text-center">🌱 Pesticide Recommendations</h2>
        
        <div class="mb-4">
            <p><strong>Crop:</strong> <?= htmlspecialchars($crop) ?></p>
            <p><strong>Growth Stage:</strong> <?= htmlspecialchars($growth_stage) ?></p>
        </div>
        
        <h5 class="mb-3">Recommended Pesticides:</h5>
        <ul class="pesticide-list">
            <?php foreach ($recommendations as $pesticide): ?>
                <li><?= htmlspecialchars($pesticide) ?></li>
            <?php endforeach; ?>
        </ul>
        
        <div class="text-center mt-4">
            <a href="pesticide.php" class="btn btn-primary">New Recommendation</a>
            <a href="home.php" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>
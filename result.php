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

// --------- Crop data and logic ---------
$crops = [
    "Rice" => [
        "ph_low" => 5, "ph_high" => 7, "soil" => ["Clayey", "Silty"], 
        "season" => ["Kharif"], "rainfall_min" => 500
    ],
    "Wheat" => [
        "ph_low" => 6, "ph_high" => 7.5, "soil" => ["Loamy", "Clayey"], 
        "season" => ["Rabi"], "rainfall_min" => 0
    ],
    "Maize" => [
        "ph_low" => 5.5, "ph_high" => 7, "soil" => ["Loamy", "Sandy"], 
        "season" => ["Kharif", "Rabi"], "rainfall_min" => 400
    ],
    "Cotton" => [
        "ph_low" => 6, "ph_high" => 8, "soil" => ["Sandy", "Loamy"], 
        "season" => ["Kharif"], "rainfall_min" => 500
    ],
    "Barley" => [
        "ph_low" => 6, "ph_high" => 8, "soil" => ["Loamy"], 
        "season" => ["Rabi"], "rainfall_min" => 0
    ],
    "Soybean" => [
        "ph_low" => 6, "ph_high" => 7.5, "soil" => ["Sandy", "Loamy"], 
        "season" => ["Kharif"], "rainfall_min" => 500
    ],
    "Sugarcane" => [
        "ph_low" => 6, "ph_high" => 8, "soil" => ["Loamy", "Silty"], 
        "season" => ["Kharif"], "rainfall_min" => 600
    ],
    "Potato" => [
        "ph_low" => 5.5, "ph_high" => 6.5, "soil" => ["Sandy", "Loamy"], 
        "season" => ["Rabi", "Zaid"], "rainfall_min" => 300
    ],
    "Peanut" => [
        "ph_low" => 5.5, "ph_high" => 7, "soil" => ["Sandy", "Loamy"], 
        "season" => ["Kharif", "Zaid"], "rainfall_min" => 400
    ],
    "Pumpkin" => [
        "ph_low" => 5.5, "ph_high" => 7, "soil" => ["Loamy", "Sandy"], 
        "season" => ["Zaid"], "rainfall_min" => 300
    ],
    "Lentil" => [
        "ph_low" => 6, "ph_high" => 7.5, "soil" => ["Silty", "Loamy"], 
        "season" => ["Rabi"], "rainfall_min" => 200
    ],
];

// --------- User input handling ---------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get farmer details from session
    $farmer_name = $_SESSION['farmer_name'] ?? 'Unknown';
    $farmer_residence = $_SESSION['farmer_residence'] ?? 'Unknown';

    // Validate and sanitize inputs
    $ph = filter_input(INPUT_POST, 'ph', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 3, 'max_range' => 10]]);
    $nitrogen = filter_input(INPUT_POST, 'nitrogen', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
    $phosphorus = filter_input(INPUT_POST, 'phosphorus', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
    $potassium = filter_input(INPUT_POST, 'potassium', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
    $soil_type = htmlspecialchars($_POST['soil_type']);
    $season = htmlspecialchars($_POST['season']);
    $humidity = filter_input(INPUT_POST, 'humidity', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
    $rainfall = filter_input(INPUT_POST, 'rainfall', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);

    // Check if any validation failed
    if ($ph === false || $nitrogen === false || $phosphorus === false || $potassium === false || 
        empty($soil_type) || empty($season) || $humidity === false || $rainfall === false) {
        die("Invalid input data. Please go back and enter valid values.");
    }

    $recommendations = [];
    $not_suitable_note = "";

    // First, try to find strictly matching crops
    foreach ($crops as $crop => $cond) {
        if (
            $ph >= $cond["ph_low"] && $ph <= $cond["ph_high"] &&
            in_array($soil_type, $cond["soil"]) &&
            in_array($season, $cond["season"]) &&
            $rainfall >= $cond["rainfall_min"]
        ) {
            $recommendations[] = $crop;
        }
        if (count($recommendations) >= 10) break; // limit to 10
    }

    // If no strict match, recommend "closest" crop with best partial score
    if (empty($recommendations)) {
        $best_crop = "";
        $best_score = -1;
        foreach ($crops as $crop => $cond) {
            $score = 0;
            if ($ph >= $cond["ph_low"] && $ph <= $cond["ph_high"]) $score++;
            if (in_array($soil_type, $cond["soil"])) $score++;
            if (in_array($season, $cond["season"])) $score++;
            if ($rainfall >= $cond["rainfall_min"]) $score++;
            if ($score > $best_score) {
                $best_score = $score;
                $best_crop = $crop;
            }
        }
        if ($best_crop) {
            $recommendations[] = $best_crop;
            $not_suitable_note = "Note: No crop is perfectly suitable for your input values. However, here is the closest possible recommendation based on your data. Adjust your soil conditions or try different crops for better results.";
        }
    }

    // Store data in database
    $recommended_crops_str = implode(", ", $recommendations);
    
    $stmt = $conn->prepare("INSERT INTO recommendations (
        farmer_name, farmer_residence, ph, nitrogen, phosphorus, potassium, 
        soil_type, season, humidity, rainfall, recommended_crops
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssddddssdds", 
        $farmer_name, $farmer_residence, $ph, $nitrogen, $phosphorus, $potassium,
        $soil_type, $season, $humidity, $rainfall, $recommended_crops_str);
    
    if (!$stmt->execute()) {
        error_log("Database error: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crop Recommendations</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2a5298;
            --secondary-color: #1a3e72;
            --accent-color: #4CAF50;
            --light-bg: #f8f9fa;
            --card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            --text-color: #3a4a6b;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding: 2rem 0;
            color: var(--text-color);
        }
        
        .container {
            max-width: 800px;
            animation: fadeIn 0.5s ease-out;
        }
        
        .result-card {
            background: white;
            box-shadow: var(--card-shadow);
            border-radius: 18px;
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.15);
        }
        
        .result-title {
            font-weight: 700;
            margin-bottom: 0.5em;
            color: var(--primary-color);
            font-size: 2.2rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .section-title {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .farmer-info {
            background: var(--light-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            border-color: #c8e6c9;
            color: #2e7d32;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        
        .alert-warning {
            border-radius: 12px;
        }
        
        .crop-list {
            font-size: 1.1rem;
            margin-left: 1.5rem;
        }
        
        .crop-list li {
            margin-bottom: 0.5rem;
        }
        
        .input-summary {
            margin-top: 1.5rem;
        }
        
        .input-summary table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .input-summary th {
            background: #e9ecef;
            color: var(--secondary-color);
            font-weight: 500;
            padding: 0.75rem;
        }
        
        .input-summary td {
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
        }
        
        .btn-group {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .btn {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-success:hover {
            background-color: #3d8b40;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .result-title {
                font-size: 1.8rem;
            }
            
            .result-card {
                padding: 1.5rem;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            
            .result-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .btn-group {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="result-card">
        <h2 class="result-title">🌱 Crop Recommendation Results</h2>
        
        <div class="farmer-info">
            <h5 class="section-title"><i class="fas fa-user"></i> Farmer Information</h5>
            <p><strong>Name:</strong> <?= htmlspecialchars($_SESSION['farmer_name'] ?? 'Unknown') ?></p>
            <p><strong>Residence:</strong> <?= htmlspecialchars($_SESSION['farmer_residence'] ?? 'Unknown') ?></p>
        </div>

        <div class="alert alert-success">
            <h5 class="section-title"><i class="fas fa-check-circle"></i> Recommended Crops:</h5>
            <ol class="crop-list">
                <?php foreach ($recommendations as $crop): ?>
                    <li><?= htmlspecialchars($crop) ?></li>
                <?php endforeach; ?>
            </ol>
            <?php if ($not_suitable_note): ?>
                <div class="alert alert-warning mt-3"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($not_suitable_note) ?></div>
            <?php endif; ?>
        </div>

        <div class="input-summary">
            <h5 class="section-title"><i class="fas fa-clipboard-list"></i> Your Input Summary</h5>
            <table class="table table-bordered">
                <tr>
                    <th>Soil pH</th>
                    <td><?= htmlspecialchars($ph) ?></td>
                    <th>Nitrogen (kg/ha)</th>
                    <td><?= htmlspecialchars($nitrogen) ?></td>
                </tr>
                <tr>
                    <th>Phosphorus (kg/ha)</th>
                    <td><?= htmlspecialchars($phosphorus) ?></td>
                    <th>Potassium (kg/ha)</th>
                    <td><?= htmlspecialchars($potassium) ?></td>
                </tr>
                <tr>
                    <th>Soil Type</th>
                    <td><?= htmlspecialchars($soil_type) ?></td>
                    <th>Season</th>
                    <td><?= htmlspecialchars($season) ?></td>
                </tr>
                <tr>
                    <th>Humidity (%)</th>
                    <td><?= htmlspecialchars($humidity) ?></td>
                    <th>Rainfall (mm/year)</th>
                    <td><?= htmlspecialchars($rainfall) ?></td>
                </tr>
            </table>
        </div>

        <div class="btn-group">
            <a href="home.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back to Input</a>
            <button onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Print Results</button>
            <a href="farmerd.php" class="btn btn-secondary"><i class="fas fa-user-plus"></i> New Farmer</a>
        </div>
    </div>
    
    <footer class="text-center pt-4 text-muted fs-6">Powered by Smart Agriculture Technology &mdash; <?php echo date("Y"); ?></footer>
</div>

<script>
    // Add animation to elements when they come into view
    document.addEventListener('DOMContentLoaded', function() {
        const elements = document.querySelectorAll('.farmer-info, .alert-success, .input-summary');
        
        elements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'all 0.6s ease-out';
            
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 100);
        });
    });
</script>
</body>
</html>
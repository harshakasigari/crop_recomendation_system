<?php
session_start();
// Database configuration
$db_host = 'localhost';
$db_user = 'root'; // default XAMPP username
$db_pass = '';     // default XAMPP password
$db_name = 'csp';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Crop Recommendation System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
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
        
        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.15);
        }
        
        .app-title {
            font-weight: 700;
            margin-bottom: 0.5em;
            color: var(--primary-color);
            font-size: 2.2rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .form-select, .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(42, 82, 152, 0.15);
        }
        
        .btn-success {
            background-color: var(--accent-color);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(76, 175, 80, 0.2);
        }
        
        .btn-success:hover {
            background-color: #3d8b40;
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(76, 175, 80, 0.3);
        }
        
        .lead {
            color: #5a6a85;
            font-weight: 400;
            line-height: 1.6;
        }
        
        footer {
            color: #7a8ba9;
            font-size: 0.9rem;
            margin-top: 2rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .app-title {
                font-size: 1.8rem;
            }
            
            .form-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center pb-4">
        <h2 class="app-title">🌱 Crop Recommendation System</h2>
        <p class="lead text-secondary">Enter your soil and environment data below to get precise crop recommendations for your farm.</p>
    </div>
    <div class="card form-card p-4">
        <form method="POST" action="result.php" autocomplete="off" novalidate>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Soil pH</label>
                    <input type="number" name="ph" step="0.01" min="3" max="10" class="form-control" >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nitrogen (kg/ha)</label>
                    <input type="number" name="nitrogen" step="0.01" min="0" class="form-control" >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phosphorus (kg/ha)</label>
                    <input type="number" name="phosphorus" step="0.01" min="0" class="form-control" >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Potassium (kg/ha)</label>
                    <input type="number" name="potassium" step="0.01" min="0" class="form-control" >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Soil Type</label>
                    <select name="soil_type" class="form-select" required>
                        <option value="" disabled selected>Select soil type</option>
                        <option value="Loamy">Loamy</option>
                        <option value="Sandy">Sandy</option>
                        <option value="Clayey">Clayey</option>
                        <option value="Silty">Silty</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Season</label>
                    <select name="season" class="form-select" required>
                        <option value="" disabled selected>Select season</option>
                        <option value="Kharif">Kharif (Monsoon)</option>
                        <option value="Rabi">Rabi (Winter)</option>
                        <option value="Zaid">Zaid (Summer)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Humidity (%)</label>
                    <input type="number" name="humidity" step="0.1" min="0" max="100" class="form-control" required value="65" placeholder="e.g., 65">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rainfall (mm/year)</label>
                    <input type="number" name="rainfall" step="0.1" min="0" class="form-control" required value="600" placeholder="e.g., 600">
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success mt-3">🌾 Get Recommendations</button>
            </div>
        </form>
    </div>
    <footer class="text-center pt-4 text-muted fs-6">Powered by Smart Agriculture Technology &mdash; <?php echo date("Y"); ?></footer>
</div>
<script>
    // Enhanced client-side form validation with Bootstrap style
    (function(){
        const forms = document.querySelectorAll('form');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>
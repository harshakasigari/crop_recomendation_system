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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $farmer_name = htmlspecialchars($_POST['farmer_name']);
    $farmer_residence = htmlspecialchars($_POST['farmer_residence']);
    
    // Store in session
    $_SESSION['farmer_name'] = $farmer_name;
    $_SESSION['farmer_residence'] = $farmer_residence;
    
    // Store in database
    $stmt = $conn->prepare("INSERT INTO crop (farmer_name, farmer_residence) VALUES (?, ?)");
    $stmt->bind_param("ss", $farmer_name, $farmer_residence);
    
    if ($stmt->execute()) {
        header("Location: home.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Farmer Registration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,#e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .container { max-width: 650px;}
        .card { border-radius: 18px; box-shadow: 0 4px 18px 6px rgba(0,0,0,0.09);}
        .app-title { color: #19647e; font-weight: bold; font-size: 2rem; text-align: center;}
        label { font-weight: 500; color: #35477d;}
        .form-section-title { font-size: 1.1rem; font-weight: 400; color: #30506d; text-align: center;}
        .btn-primary { border-radius: 18px; font-size: 1rem; padding-left:1.8em; padding-right:1.8em;}
        .form-label { font-weight: 500; }
        .form-control, .form-select { border-radius: 12px;}
        .footer-note { font-size:0.9rem; color:#6884b8; text-align:center;}
        .form-card {
            box-shadow: 0 4px 18px 6px rgba(0,0,0,0.07), 0 1.5px 5px 0 rgba(0,0,0,0.03);
            border-radius: 18px;
        }
        .animated-form {
            animation: fadeInDown 1s both;
        }
        @keyframes fadeInDown {
            0% { opacity:0; transform: translateY(-40px);}
            100% { opacity:1; transform: none;}
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row d-flex justify-content-center">
        <div class="col-lg-8 col-md-10 animated-form">
            <div class="card p-5 mt-4 form-card">
                <div class="app-title mb-2">👨‍🌾 Farmer Registration</div>
                <div class="form-section-title">Enter Farmer Details</div>
                <form method="POST" action="farmerd.php" autocomplete="off" novalidate>
                    <div class="mb-4">
                        <label for="farmer_name" class="form-label">Farmer Name</label>
                        <input type="text" name="farmer_name" id="farmer_name" class="form-control" placeholder="Enter full name" required maxlength="80">
                    </div>
                    <div class="mb-4">
                        <label for="farmer_residence" class="form-label">Farmer Residence</label>
                        <input type="text" name="farmer_residence" id="farmer_residence" class="form-control" placeholder="Village / Town / District" required maxlength="100">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary px-5">Next</button>
                    </div>
                </form>
                <div class="footer-note pt-4">Your information is safe and helps provide accurate crop recommendations.</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
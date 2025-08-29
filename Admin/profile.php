<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Database connection
include('../db_connect.php'); // Adjust this path as per your setup

// Fetch admin details
$sql = "SELECT * FROM admin_details WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$profile = $result->fetch_assoc();

// If profile not found, redirect to the add/edit page
if (!$profile) {
    header("Location: add_admin_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <style>
        :root {
            --primary-blue: #1E3A8A;
            --secondary-teal: #2DD4BF;
            --accent-green: #10B981;
            --neutral-gray: #F7FAFC;
            --text-dark: #1A202C;
            --shadow-teal: rgba(45, 212, 191, 0.25);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        .card {
            border: none;
            border-radius: 12px;
            background: var(--neutral-gray);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px var(--shadow-teal);
        }
        .btn-primary {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-teal));
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-teal), var(--primary-blue));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-teal);
        }
        .back-btn {
            background: linear-gradient(to right, #6B7280, #4B5563);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: linear-gradient(to right, #4B5563, #6B7280);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.25);
        }
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: var(--secondary-teal) var(--neutral-gray);
        }
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--secondary-teal);
            border-radius: 4px;
        }
        .dropdown-content {
            transition: max-height 0.3s ease, opacity 0.3s ease;
        }
        .dropdown-content.max-h-0 {
            max-height: 0;
            opacity: 0;
        }
        .dropdown-content.max-h-96 {
            max-height: 24rem;
            opacity: 1;
        }
        .nav-link {
            transition: all 0.2s ease-in-out;
        }
        .nav-link:hover {
            transform: translateX(5px) scale(1.02);
            background-color: var(--shadow-teal);
            color: var(--primary-blue);
        }
        .dropdown-toggle i {
            transition: transform 0.3s ease;
        }
        .dropdown-toggle.active i {
            transform: rotate(90deg);
        }
        .logout-link:hover {
            background-color: #EF4444;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .sidebar {
            animation: slideIn 0.5s ease-out;
        }
        body {
            background: #F3F4F6;
        }
        .main-content {
            margin-left: 16rem;
            transition: margin-left 0.3s ease;
        }
        #sidebar-toggle {
            display: none;
        }
        #sidebar-toggle:checked ~ .sidebar {
            transform: translateX(0);
        }
        #sidebar-toggle:checked ~ .main-content {
            margin-left: 0;
        }
        #sidebar-toggle:checked ~ .overlay {
            display: block;
        }
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10;
        }
        @media (max-width: 767px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-toggle {
                display: block;
            }
        }
        .profile-img {
            max-width: 200px;
            border-radius: 12px;
            border: 2px solid var(--secondary-teal);
        }
        .profile-info {
            color: var(--text-dark);
        }
        .profile-label {
            font-weight: 500;
            color: var(--text-dark);
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar Toggle -->
    <input type="checkbox" id="sidebar-toggle" class="hidden">
    <label for="sidebar-toggle" class="mobile-toggle md:hidden p-4 bg-[var(--primary-blue)] text-white flex justify-between items-center cursor-pointer">
        <span class="text-xl fw-semibold">Admin Profile</span>
        <i class="fas fa-bars text-xl"></i>
    </label>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Overlay for mobile -->
    <div class="overlay"></div>

    <!-- Main Content -->
    <div class="main-content p-5">
        <div class="container mt-5 mb-5">
            <h1 class="mb-4 text-center fw-bold text-[var(--text-dark)]">Admin Profile</h1>
            <a href="admin_dashboard.php" class="btn back-btn text-white mb-4">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>

            <!-- Profile Card -->
            <div class="card shadow p-4 fade-in">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php if (!empty($profile['profile_photo'])): ?>
                            <img src="<?= htmlspecialchars($profile['profile_photo']) ?>" alt="Profile Photo" class="profile-img mb-3">
                        <?php else: ?>
                            <p class="text-gray-500 mb-3"><em>No profile photo available</em></p>
                        <?php endif; ?>
                        <?php if (!empty($profile['logo'])): ?>
                            <img src="<?= htmlspecialchars($profile['logo']) ?>" alt="Logo" class="profile-img mb-3">
                        <?php else: ?>
                            <p class="text-gray-500 mb-3"><em>No logo available</em></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h2 class="card-title mb-3 fw-semibold"><?= htmlspecialchars($profile['full_name']) ?></h2>
                        <div class="profile-info mb-3">
                            <span class="profile-label">Email Address:</span>
                            <span><?= htmlspecialchars($profile['email_address']) ?></span>
                        </div>
                        <div class="profile-info mb-3">
                            <span class="profile-label">Contact Number:</span>
                            <span><?= htmlspecialchars($profile['contact_number']) ?></span>
                        </div>
                        <div class="profile-info mb-3">
                            <span class="profile-label">Date of Birth:</span>
                            <span><?= htmlspecialchars($profile['date_of_birth']) ?></span>
                        </div>
                        <div class="profile-info mb-3">
                            <span class="profile-label">Gender:</span>
                            <span><?= htmlspecialchars($profile['gender']) ?></span>
                        </div>
                        <div class="profile-info mb-3">
                            <span class="profile-label">Subjects Taught:</span>
                            <span><?= htmlspecialchars($profile['subjects_taught']) ?></span>
                        </div>
                        <div class="profile-info mb-3">
                            <span class="profile-label">Certifications/Degrees:</span>
                            <span><?= htmlspecialchars($profile['certifications']) ?></span>
                        </div>
                        <div class="profile-info mb-3">
                            <span class="profile-label">Years of Experience:</span>
                            <span><?= htmlspecialchars($profile['years_of_experience']) ?> years</span>
                        </div>
                        <a href="add_admin_profile.php" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
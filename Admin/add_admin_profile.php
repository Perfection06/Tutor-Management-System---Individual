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

// Check if a profile exists for the admin
$sql = "SELECT * FROM admin_details WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$profile = $result->fetch_assoc();

$sql = "
    SELECT ad.*, a.username 
    FROM admin_details ad
    JOIN Admin a ON ad.admin_id = a.admin_id
    WHERE ad.admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $contact_number = $_POST['contact_number'];
    $email_address = $_POST['email_address'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $new_password = $_POST['new_password']; // New password field

    $subjects_taught = implode(', ', $_POST['subjects_taught']); // Combine inputs into a single string
    $certifications = implode(', ', $_POST['certifications']); // Combine inputs into a single string
    $years_of_experience = $_POST['years_of_experience'];

    $profile_photo = $profile['profile_photo'];
    $logo = $profile['logo'];

    // Handle file uploads
    if (!empty($_FILES['profile_photo']['name'])) {
        $profile_photo = '../uploads/' . time() . '_' . $_FILES['profile_photo']['name'];
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $profile_photo);
    }

    if (!empty($_FILES['logo']['name'])) {
        $logo = '../uploads/' . time() . '_' . $_FILES['logo']['name'];
        move_uploaded_file($_FILES['logo']['tmp_name'], $logo);
    }

    // Update admin details
    if ($profile) {
        $sql = "UPDATE admin_details 
                SET full_name = ?, contact_number = ?, email_address = ?, date_of_birth = ?, gender = ?, 
                    subjects_taught = ?, years_of_experience = ?, certifications = ?, profile_photo = ?, logo = ? 
                WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssisssi', $full_name, $contact_number, $email_address, $date_of_birth, $gender, 
                          $subjects_taught, $years_of_experience, $certifications, $profile_photo, $logo, $admin_id);
        $stmt->execute();
    }

    // Update username and password if provided
    if (!empty($username) || !empty($new_password)) {
        $sql = "UPDATE Admin SET username = ?, password = ? WHERE admin_id = ?";
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $username, $hashed_password, $admin_id);
        $stmt->execute();
    }

    if ($stmt->execute()) {
        echo "Profile saved successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-blue: #1E3A8A;
            --secondary-green: #10B981;
            --accent-teal: #2DD4BF;
            --neutral-gray: #F7FAFC;
            --text-dark: #1A202C;
            --accent-shadow: rgba(45, 212, 191, 0.2);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        .btn-gradient {
            background: linear-gradient(to right, var(--primary-blue), var(--accent-teal));
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            background: linear-gradient(to right, var(--accent-teal), var(--primary-blue));
            transform: translateY(-2px);
        }
        .container {
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--accent-teal) var(--neutral-gray);
        }
        .container::-webkit-scrollbar {
            width: 8px;
        }
        .container::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .container::-webkit-scrollbar-thumb {
            background: var(--accent-teal);
            border-radius: 4px;
        }
        input:not([type="radio"]):not([type="file"]), select {
            transition: all 0.2s ease;
        }
        input:not([type="radio"]):not([type="file"]):hover, select:hover {
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 2px var(--accent-shadow);
        }
        .image-preview {
            max-width: 120px;
            max-height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 0.5rem;
            border: 2px solid var(--neutral-gray);
        }
        .radio-label {
            transition: all 0.2s ease;
        }
        input[type="radio"]:checked + .radio-label {
            background: var(--accent-teal);
            color: white;
            box-shadow: 0 2px 4px var(--accent-shadow);
        }
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: var(--accent-teal) var(--neutral-gray);
        }
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--accent-teal);
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
            background-color: var(--accent-shadow);
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
            background: var(--neutral-gray);
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
    </style>
    <script>
        function addInput(sectionId, inputName, labelText) {
            const section = document.getElementById(sectionId);
            const div = document.createElement('div');
            div.classList.add('flex', 'items-center', 'mt-2', 'fade-in', 'dynamic-inputs');

            const input = document.createElement('input');
            input.type = 'text';
            input.name = inputName + '[]';
            input.placeholder = labelText;
            input.required = true;
            input.classList.add('w-full', 'p-2', 'border', 'border-gray-300', 'rounded-md', 'focus:ring-2', 'focus:ring-[var(--accent-teal)]', 'text-sm', 'bg-white');

            const removeButton = document.createElement('span');
            removeButton.innerText = 'Remove';
            removeButton.classList.add('ml-2', 'text-red-500', 'hover:text-red-700', 'cursor-pointer', 'text-xs', 'font-medium');
            removeButton.setAttribute('aria-label', 'Remove ' + labelText);
            removeButton.onclick = () => {
                section.removeChild(div);
                if (!section.querySelector('.dynamic-inputs')) {
                    section.classList.remove('space-y-2');
                }
            };

            div.appendChild(input);
            div.appendChild(removeButton);
            section.appendChild(div);
            section.classList.add('space-y-2');
        }

        function previewImage(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    if (!file.type.startsWith('image/')) {
                        showMessage('Please select a valid image file.', 'error');
                        input.value = '';
                        preview.src = '';
                        preview.classList.add('hidden');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '';
                    preview.classList.add('hidden');
                }
            });
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `p-3 rounded-lg text-center text-sm fade-in shadow-md ${
                type === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
            }`;
            messageDiv.innerText = message;
            document.querySelector('.container').prepend(messageDiv);
            setTimeout(() => messageDiv.remove(), 3000);
        }

        window.onload = function() {
            previewImage('profile_photo', 'profile-photo-preview');
            previewImage('logo', 'logo-preview');

            const form = document.getElementById('adminProfileForm');
            form.addEventListener('submit', function(e) {
                const email = document.getElementById('email_address').value;
                const contact = document.getElementById('contact_number').value;
                const years = document.getElementById('years_of_experience').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const phoneRegex = /^\+?\d{7,15}$/;

                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    showMessage('Please enter a valid email address.', 'error');
                    return;
                }
                if (!phoneRegex.test(contact)) {
                    e.preventDefault();
                    showMessage('Please enter a valid contact number (7-15 digits).', 'error');
                    return;
                }
                if (years < 0) {
                    e.preventDefault();
                    showMessage('Years of experience cannot be negative.', 'error');
                    return;
                }

                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            });

            <?php if (isset($_SESSION['message'])): ?>
                showMessage("<?php echo htmlspecialchars($_SESSION['message']); ?>", 
                    "<?php echo strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success'; ?>");
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        };
    </script>
</head>
<body class="bg-[var(--neutral-gray)] font-sans text-[var(--text-dark)] min-h-screen flex">
    <!-- Mobile Sidebar Toggle -->
    <input type="checkbox" id="sidebar-toggle" class="hidden">
    <label for="sidebar-toggle" class="mobile-toggle md:hidden p-4 bg-[var(--primary-blue)] text-white flex justify-between items-center cursor-pointer">
        <span class="text-xl font-semibold">Manage Admin Profile</span>
        <i class="fas fa-bars text-xl"></i>
    </label>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Overlay for mobile -->
    <div class="overlay"></div>

    <!-- Main Content -->
    <div class="main-content p-4">
        <div class="container bg-white rounded-xl shadow-2xl max-w-5xl w-full p-8 fade-in">
            <h1 class="text-3xl font-bold text-center text-[var(--primary-blue)] mb-6 tracking-tight">Manage Admin Profile</h1>

            <form id="adminProfileForm" action="add_admin_profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))) ?>">
                <!-- Personal Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-[var(--text-dark)] mb-3">Personal Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="full_name" class="block text-xs font-medium text-gray-600">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($profile['full_name']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" />
                        </div>
                        <div>
                            <label for="contact_number" class="block text-xs font-medium text-gray-600">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($profile['contact_number']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" />
                        </div>
                        <div>
                            <label for="email_address" class="block text-xs font-medium text-gray-600">Email Address</label>
                            <input type="email" id="email_address" name="email_address" value="<?= htmlspecialchars($profile['email_address']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" />
                        </div>
                        <div>
                            <label for="date_of_birth" class="block text-xs font-medium text-gray-600">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($profile['date_of_birth']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" />
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-600">Gender</label>
                            <div class="flex gap-3 mt-2">
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Male" <?= $profile['gender'] === 'Male' ? 'checked' : '' ?> class="hidden" />
                                    <span class="radio-label px-3 py-1 rounded-md bg-gray-100 text-sm font-medium text-gray-700 hover:bg-gray-200 cursor-pointer <?= $profile['gender'] === 'Male' ? 'bg-[var(--accent-teal)] text-white' : '' ?>">Male</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Female" <?= $profile['gender'] === 'Female' ? 'checked' : '' ?> class="hidden" />
                                    <span class="radio-label px-3 py-1 rounded-md bg-gray-100 text-sm font-medium text-gray-700 hover:bg-gray-200 cursor-pointer <?= $profile['gender'] === 'Female' ? 'bg-[var(--accent-teal)] text-white' : '' ?>">Female</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Other" <?= $profile['gender'] === 'Other' ? 'checked' : '' ?> class="hidden" />
                                    <span class="radio-label px-3 py-1 rounded-md bg-gray-100 text-sm font-medium text-gray-700 hover:bg-gray-200 cursor-pointer <?= $profile['gender'] === 'Other' ? 'bg-[var(--accent-teal)] text-white' : '' ?>">Other</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Details and Account Details -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-[var(--text-dark)] mb-3">Professional Details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div id="subjects-section" class="space-y-2">
                            <label class="block text-xs font-medium text-gray-600">Subjects Taught</label>
                            <?php
                            $subjects = !empty($profile['subjects_taught']) ? explode(', ', $profile['subjects_taught']) : [''];
                            foreach ($subjects as $subject) {
                                echo '<div class="flex items-center mt-2 dynamic-inputs">';
                                echo '<input type="text" name="subjects_taught[]" value="' . htmlspecialchars($subject) . '" placeholder="Subjects Taught" required class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white">';
                                if (count($subjects) > 1 || $subject !== '') {
                                    echo '<span class="ml-2 text-red-500 hover:text-red-700 cursor-pointer text-xs font-medium" onclick="this.parentElement.remove()" aria-label="Remove Subject">Remove</span>';
                                }
                                echo '</div>';
                            }
                            ?>
                            <span class="inline-block bg-[var(--secondary-green)] text-white px-3 py-1 rounded-md hover:bg-green-600 cursor-pointer text-xs font-medium mt-2"
                                onclick="addInput('subjects-section', 'subjects_taught', 'Subjects Taught')">Add Subject</span>
                        </div>
                        <div id="certifications-section" class="space-y-2">
                            <label class="block text-xs font-medium text-gray-600">Certifications/Degrees</label>
                            <?php
                            $certs = !empty($profile['certifications']) ? explode(', ', $profile['certifications']) : [''];
                            foreach ($certs as $cert) {
                                echo '<div class="flex items-center mt-2 dynamic-inputs">';
                                echo '<input type="text" name="certifications[]" value="' . htmlspecialchars($cert) . '" placeholder="Certifications/Degrees" required class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white">';
                                if (count($certs) > 1 || $cert !== '') {
                                    echo '<span class="ml-2 text-red-500 hover:text-red-700 cursor-pointer text-xs font-medium" onclick="this.parentElement.remove()" aria-label="Remove Certification">Remove</span>';
                                }
                                echo '</div>';
                            }
                            ?>
                            <span class="inline-block bg-[var(--secondary-green)] text-white px-3 py-1 rounded-md hover:bg-green-600 cursor-pointer text-xs font-medium mt-2"
                                onclick="addInput('certifications-section', 'certifications', 'Certifications/Degrees')">Add Certification</span>
                        </div>
                        <div>
                            <label for="years_of_experience" class="block text-xs font-medium text-gray-600">Years of Experience</label>
                            <input type="number" id="years_of_experience" name="years_of_experience" min="0" value="<?= htmlspecialchars($profile['years_of_experience']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" />
                        </div>
                    </div>

                    <h2 class="text-lg font-semibold text-[var(--text-dark)] mt-4 mb-3">Account Details</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="username" class="block text-xs font-medium text-gray-600">Username</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($profile['username']) ?>" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" />
                        </div>
                        <div>
                            <label for="new_password" class="block text-xs font-medium text-gray-600">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Enter new password"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[var(--accent-teal)] text-sm bg-white" />
                        </div>
                    </div>
                </div>

                <!-- Custom Branding -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-[var(--text-dark)] mb-3">Custom Branding</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="profile_photo" class="block text-xs font-medium text-gray-600">Profile Photo</label>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*"
                                class="w-full p-2 border border-gray-300 rounded-md file:bg-[var(--primary-blue)] file:text-white file:border-none file:rounded-md file:px-3 file:py-1 text-sm" />
                            <img id="profile-photo-preview" src="<?= htmlspecialchars($profile['profile_photo']) ?>" class="image-preview <?= empty($profile['profile_photo']) ? 'hidden' : '' ?>" alt="Profile Photo Preview" />
                        </div>
                        <div>
                            <label for="logo" class="block text-xs font-medium text-gray-600">Logo</label>
                            <input type="file" id="logo" name="logo" accept="image/*"
                                class="w-full p-2 border border-gray-300 rounded-md file:bg-[var(--primary-blue)] file:text-white file:border-none file:rounded-md file:px-3 file:py-1 text-sm" />
                            <img id="logo-preview" src="<?= htmlspecialchars($profile['logo']) ?>" class="image-preview <?= empty($profile['logo']) ? 'hidden' : '' ?>" alt="Logo Preview" />
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn-gradient text-white px-6 py-2 rounded-md text-sm font-medium shadow-md">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

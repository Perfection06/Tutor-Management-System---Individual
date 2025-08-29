
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-blue: #1E3A8A;
            --secondary-teal: #2DD4BF;
            --accent-green: #10B981;
            --neutral-gray: #F7FAFC;
            --text-dark: #1A202C;
            --shadow-teal: rgba(45, 212, 191, 0.25);
        }
        .sd-sidebar {
            scrollbar-width: thin;
            scrollbar-color: var(--secondary-teal) var(--neutral-gray);
            animation: sd-slideIn 0.5s ease-out;
        }
        .sd-sidebar::-webkit-scrollbar {
            width: 8px;
        }
        .sd-sidebar::-webkit-scrollbar-track {
            background: var(--neutral-gray);
        }
        .sd-sidebar::-webkit-scrollbar-thumb {
            background: var(--secondary-teal);
            border-radius: 4px;
        }
        .sd-dropdown-content {
            transition: max-height 0.3s ease, opacity 0.3s ease;
        }
        .sd-dropdown-content.sd-max-h-0 {
            max-height: 0;
            opacity: 0;
        }
        .sd-dropdown-content.sd-max-h-96 {
            max-height: 24rem;
            opacity: 1;
        }
        .sd-nav-link {
            transition: all 0.2s ease-in-out;
        }
        .sd-nav-link:hover {
            transform: translateX(5px) scale(1.02);
            background-color: var(--shadow-teal);
            color: var(--primary-blue);
        }
        .sd-dropdown-toggle i {
            transition: transform 0.3s ease;
        }
        .sd-dropdown-toggle.sd-active i {
            transform: rotate(90deg);
        }
        .sd-logout-link:hover {
            background-color: #EF4444;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        @keyframes sd-slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>

    <div class="sd-sidebar fixed top-0 left-0 h-screen w-64 bg-[var(--neutral-gray)] p-6 overflow-y-auto shadow-lg z-20">
        <h3 class="text-lg font-semibold text-[var(--text-dark)] mb-6">Navigation</h3>
        <a href="./admin_dashboard.php" class="sd-nav-link block p-3 text-[var(--text-dark)] rounded-lg flex items-center">
            <i class="fas fa-home mr-2 text-[var(--secondary-teal)]"></i>Home
        </a>
        <a href="./profile.php" class="sd-nav-link block p-3 text-[var(--text-dark)] rounded-lg flex items-center">
            <i class="fas fa-user-edit mr-2 text-[var(--secondary-teal)]"></i>Profile
        </a>
        <a href="./schedule_availability.php" class="sd-nav-link block p-3 text-[var(--text-dark)] rounded-lg flex items-center">
            <i class="fas fa-calendar-day mr-2 text-[var(--secondary-teal)]"></i>Free Day Schedule
        </a>

        <div class="mt-4">
            <button class="sd-dropdown-toggle flex items-center justify-between w-full p-3 text-[var(--text-dark)] font-medium rounded-lg hover:bg-[var(--shadow-teal)] transition-colors duration-200" onclick="sdToggleDropdown('course')">
                <span><i class="fas fa-book mr-2 text-[var(--secondary-teal)]"></i>Course</span>
                <i class="fas fa-chevron-right"></i>
            </button>
            <div id="course" class="sd-dropdown-content sd-max-h-0 overflow-hidden">
                <a href="./add_course.php" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2 text-[var(--secondary-teal)]"></i>Add Course
                </a>
            </div>
        </div>

        <div class="mt-4">
            <button class="sd-dropdown-toggle flex items-center justify-between w-full p-3 text-[var(--text-dark)] font-medium rounded-lg hover:bg-[var(--shadow-teal)] transition-colors duration-200" onclick="sdToggleDropdown('tuitions')">
                <span><i class="fas fa-layer-group mr-2 text-[var(--secondary-teal)]"></i>Tuitions</span>
                <i class="fas fa-chevron-right"></i>
            </button>
            <div id="tuitions" class="sd-dropdown-content sd-max-h-0 overflow-hidden">
                <a href="./add_grade.php" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-layer-group mr-2 text-[var(--secondary-teal)]"></i>Add Grade
                </a>
                <a href="./add_subject.php" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-book-open mr-2 text-[var(--secondary-teal)]"></i>Add Subject
                </a>
                <a href="./create_class.php" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-chalkboard mr-2 text-[var(--secondary-teal)]"></i>Create Class
                </a>
            </div>
        </div>

        <div class="mt-4">
            <button class="sd-dropdown-toggle flex items-center justify-between w-full p-3 text-[var(--text-dark)] font-medium rounded-lg hover:bg-[var(--shadow-teal)] transition-colors duration-200" onclick="sdToggleDropdown('students')">
                <span><i class="fas fa-users mr-2 text-[var(--secondary-teal)]"></i>Students</span>
                <i class="fas fa-chevron-right"></i>
            </button>
            <div id="students" class="sd-dropdown-content sd-max-h-0 overflow-hidden">
                <a href="./add_student.php" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-user-plus mr-2 text-[var(--secondary-teal)]"></i>Add Student
                </a>
                <a href="./view_students.php" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-users mr-2 text-[var(--secondary-teal)]"></i>View Students
                </a>
                <div class="ml-4">
                    <button class="sd-dropdown-toggle flex items-center justify-between w-full p-3 text-[var(--text-dark)] font-medium rounded-lg hover:bg-[var(--shadow-teal)] transition-colors duration-200" onclick="sdToggleDropdown('attendance')">
                        <span><i class="fas fa-check-circle mr-2 text-[var(--secondary-teal)]"></i>Attendance</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div id="attendance" class="sd-dropdown-content sd-max-h-0 overflow-hidden">
                        <a href="./class_attendance.php" class="sd-nav-link block p-3 pl-12 text-[var(--text-dark)] rounded-lg flex items-center">
                            <i class="fas fa-clipboard-list mr-2 text-[var(--secondary-teal)]"></i>Class Attendance
                        </a>
                        <a href="./course_attendance.php" class="sd-nav-link block p-3 pl-12 text-[var(--text-dark)] rounded-lg flex items-center">
                            <i class="fas fa-clipboard-check mr-2 text-[var(--secondary-teal)]"></i>Course Attendance
                        </a>
                    </div>
                </div>
                <a href="./Materials.html" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-upload mr-2 text-[var(--secondary-teal)]"></i>Upload Materials
                </a>
                <a href="./message.html" class="sd-nav-link block p-3 pl-8 text-[var(--text-dark)] rounded-lg flex items-center">
                    <i class="fas fa-envelope mr-2 text-[var(--secondary-teal)]"></i>Messages
                </a>
            </div>
        </div>

        <!-- Logout Button at the Bottom -->
        <div class="mt-auto pt-6">
            <a href="./logout.php" class="sd-logout-link block p-3 text-[var(--text-dark)] rounded-lg flex items-center transition-all duration-200">
                <i class="fas fa-sign-out-alt mr-2 text-[var(--secondary-teal)]"></i>Logout
            </a>
        </div>
    </div>

    <script>
        function sdToggleDropdown(id) {
            const element = document.getElementById(id);
            const toggleButton = element.previousElementSibling;
            element.classList.toggle('sd-max-h-0');
            element.classList.toggle('sd-max-h-96');
            toggleButton.classList.toggle('sd-active');
        }
    </script>


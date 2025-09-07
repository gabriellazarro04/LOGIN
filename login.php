<?php
session_start();
include 'Connections.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Validate inputs
    if (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill all fields and select a role";
    } else {
        // Query the database for the user
        $query = "SELECT * FROM users WHERE email = ? AND role = ?";
        $stmt = $Connections->prepare($query);
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password (using password_verify for hashed passwords)
            if (password_verify($password, $user['password'])) {
                // Password is correct, so start a new session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                // Redirect to user dashboard based on role
                switch($role) {
                    case 'driver':
                        header("Location: ./vrds/dashboard.php");
                        break;
                    case 'dispatcher':
                        header("Location: dispatcher-dashboard.php");
                        break;
                    case 'fleet-manager':
                        header("Location: fleet-manager-dashboard.php");
                        break;
                    case 'maintenance':
                        header("Location: maintenance-dashboard.php");
                        break;
                    case 'admin':
                        header("Location: admin-dashboard.php");
                        break;
                    case 'compliance':
                        header("Location: compliance-dashboard.php");
                        break;
                    default:
                        $error = "Invalid role";
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "No user found with these credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fleet Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #19004aff, #6500a4ff, #9a66ff, #dcb1ff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 1000px;
            min-height: 550px;
            display: flex;
            overflow: hidden;
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #2c3e50, #9a66ff);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .app-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .features-list {
            list-style: none;
            margin-top: 30px;
        }
        
        .features-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            font-size: 16px;
        }
        
        .features-list i {
            margin-right: 10px;
            background: rgba(255, 255, 255, 0.2);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .panel {
            flex: 2;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            max-width: 180px;
        }
        
        h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .welcome-text {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%); 
            color: #7f8c8d;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .input-with-icon input:focus {
            border-color: #4a69bd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 105, 189, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            cursor: pointer;
        }
        
        .role-selection {
            margin-bottom: 20px;
        }
        
        .role-selection label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .role-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .role-btn {
            padding: 12px;
            background: #f8f9fa;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .role-btn:hover {
            background: #e9ecef;
        }
        
        .role-btn.selected {
            background: #4a69bd;
            color: white;
            border-color: #4a69bd;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember input {
            accent-color: #4a69bd;
        }
        
        .forgot-password {
            color: #4a69bd;
            text-decoration: none;
            font-weight: 600;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .login-button {
            width: 100%;
            padding: 14px;
            background: #4a69bd;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .login-button:hover {
            background: #3c5aa6;
            transform: translateY(-2px);
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            background: #ffeaea;
            border-radius: 5px;
            <?php if(empty($error)) echo 'display: none;'; ?>
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 10px;
        }
        
        .footer-links a {
            color: #9a66ff;
            text-decoration: none;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .left-panel {
                display: none;
            }
            
            .role-buttons {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    
    <!-- Login Container -->
    <div class="login-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="app-name">Fleet Management System</div>
            <p>Comprehensive vehicle dispatch and management solution</p>
            
            <ul class="features-list">
                <li><i class="fas fa-car"></i> Vehicle Tracking</li>
                <li><i class="fas fa-route"></i> Route Optimization</li>
                <li><i class="fas fa-users"></i> Driver Management</li>
                <li><i class="fas fa-tools"></i> Maintenance Scheduling</li>
                <li><i class="fas fa-chart-line"></i> Performance Analytics</li>
            </ul>
        </div>
        
        <!-- Right Panel -->
        <div class="panel">
            <div class="logo">
                <img src="viahale1.png" alt="Fleet System Logo">
            </div>
            
            <h2>Welcome back!</h2>
            <p class="welcome-text">Please enter your credentials to access the system</p>
            
            <form id="loginForm" method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php if(isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <span class="password-toggle" id="passwordToggle">
                            <i class="far fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="role-selection">
                    <label>Login as</label>
                    <div class="role-buttons">
                        <div class="role-btn" data-role="admin">Admin</div>
                        <div class="role-btn" data-role="driver">Driver</div>
                        <div class="role-btn" data-role="dispatcher">Dispatcher</div>
                        <div class="role-btn" data-role="fleet-manager">Fleet Manager</div>
                        <div class="role-btn" data-role="maintenance">Maintenance</div>
                        <div class="role-btn" data-role="compliance">Compliance</div>
                    </div>
                    <input type="hidden" id="selectedRole" name="role" value="<?php if(isset($_POST['role'])) echo htmlspecialchars($_POST['role']); ?>">
                </div>
                
                <div class="remember-forgot">
                    <div class="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="login-button">LOGIN</button>
                
                <div class="error-message" id="errorMessage">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="errorText"><?php echo $error; ?></span>
                </div>
            </form>
            
            <div class="demo-credentials">
                <p style="text-align: center; margin-top: 15px; font-size: 12px; color: #666;">
                    Demo: Double-click any role to auto-fill credentials
                </p>
            </div>
            
            <div class="footer">
                <p>© 2025 Fleet System. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Help Center</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                const eyeIcon = this.querySelector('i');
                eyeIcon.classList.toggle('fa-eye');
                eyeIcon.classList.toggle('fa-eye-slash');
            });
            
            // Role selection
            const roleButtons = document.querySelectorAll('.role-btn');
            const selectedRoleInput = document.getElementById('selectedRole');
            let selectedRole = "<?php if(isset($_POST['role'])) echo $_POST['role']; ?>";
            
            // Set initially selected role if exists
            if (selectedRole) {
                roleButtons.forEach(button => {
                    if (button.getAttribute('data-role') === selectedRole) {
                        button.classList.add('selected');
                    }
                });
            }
            
            // Handle role button clicks
            roleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    roleButtons.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedRole = this.getAttribute('data-role');
                    selectedRoleInput.value = selectedRole;
                });
            });
            
            // Form validation
            const loginForm = document.getElementById('loginForm');
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            
            loginForm.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const role = selectedRoleInput.value;
                
                // Simple validation
                if (!email || !password) {
                    e.preventDefault();
                    showError('Please enter both email and password');
                    return;
                }
                
                if (!role) {
                    e.preventDefault();
                    showError('Please select your role');
                    return;
                }
            });
            
            function showError(message) {
                errorText.textContent = message;
                errorMessage.style.display = 'block';
                
                // Hide error after 5 seconds
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 5000);
            }
            
            // Quick login for demo purposes (remove in production)
            // This allows clicking on a role to auto-fill and submit
            roleButtons.forEach(button => {
                button.addEventListener('dblclick', function() {
                    const role = this.getAttribute('data-role');
                    
                    // Auto-fill demo credentials based on role
                    document.getElementById('email').value = role + '@example.com';
                    document.getElementById('password').value = 'password123';
                    selectedRoleInput.value = role;
                    
                    // Highlight the selected role
                    roleButtons.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Submit the form after a short delay
                    setTimeout(() => {
                        loginForm.submit();
                    }, 500);
                });
            });
        });
    </script>
</body>
</html>
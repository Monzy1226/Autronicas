<?php
require_once __DIR__ . '/auth.php';

// If already logged in, redirect to home
if (isLoggedIn()) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Autronicas Login</title>
  <link rel="stylesheet" href="login.css" />
</head>
<body class="login-page">
  <div class="login-container">
    <!-- LEFT SIDE -->
    <div class="login-left">
      <img src="Logo.png" alt="Autronicas Logo" class="logo" />
    </div>

    <!-- RIGHT SIDE -->
    <div class="login-right">
      <div class="login-box fade-in">
        <h2>Log in to your account</h2>
        <p class="subtitle">Welcome back! Please enter your details.</p>

        <div class="input-group">
          <label for="login-username">Username</label>
          <input type="text" id="login-username" placeholder="Enter your username" />
        </div>

        <div class="input-group">
          <label for="login-password">Password</label>
          <input type="password" id="login-password" placeholder="Enter your password" />
        </div>

        <div class="remember-forgot">
          <label><input type="checkbox" id="remember-me" /> Remember me</label>
          <a href="#" class="forgot">Forgot Password</a>
        </div>

        <button id="login-btn" class="btn-primary">Sign In</button>

        <p class="signup-text">
          Don't have an account?
          <a href="register.php" class="link">Sign up</a>
        </p>
      </div>
    </div>
  </div>

  <script>
    document.getElementById("login-btn").addEventListener("click", function () {
      const username = document.getElementById("login-username").value.trim();
      const password = document.getElementById("login-password").value.trim();

      if (!username || !password) {
        alert("Please enter both username and password.");
        return;
      }

      fetch('api/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert("Login successful!");
          window.location.href = "home.php";
        } else {
          alert(data.error || "Invalid username or password.");
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert("An error occurred. Please try again.");
      });
    });
  </script>
</body>
</html>


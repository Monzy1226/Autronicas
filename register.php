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
  <title>Register - Autronicas</title>
  <link rel="stylesheet" href="register.css" />
</head>
<body class="register-page">
  <div class="register-container">
    <!-- LEFT SIDE (Register Form) -->
    <div class="register-left">
      <div class="register-box fade-in" id="registerBox">
        <h2>REGISTER NOW</h2>
        <div class="input-group">
          <input type="text" id="reg-username" placeholder="Enter your username" />
        </div>
        <div class="input-group">
          <input type="password" id="reg-password" placeholder="Enter your password" />
        </div>
        <div class="input-group">
          <input type="password" id="reg-confirm" placeholder="Confirm your password" />
        </div>
        <button id="register-btn" class="btn-register">Register Now</button>
        <p class="signup-text">
          Already have an account?
          <a href="index.php" class="link">Login now</a>
        </p>
      </div>
    </div>

    <!-- RIGHT SIDE (Logo) -->
    <div class="register-right" id="registerLogo">
      <img src="Logo.png" alt="Autronicas Logo" class="logo" />
    </div>
  </div>

  <script>
    const registerBtn = document.getElementById("register-btn");

    registerBtn.addEventListener("click", () => {
      const username = document.getElementById("reg-username").value.trim();
      const password = document.getElementById("reg-password").value.trim();
      const confirm = document.getElementById("reg-confirm").value.trim();

      if (!username || !password || !confirm) {
        alert("Please fill in all fields.");
        return;
      }

      if (password !== confirm) {
        alert("Passwords do not match!");
        return;
      }

      fetch('api/register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password, confirm })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Animation before redirect
          const box = document.getElementById("registerBox");
          const logo = document.getElementById("registerLogo");
          box.classList.add("slide-left");
          logo.classList.add("slide-right");

          setTimeout(() => {
            alert("Registration successful!");
            window.location.href = "index.php";
          }, 1000);
        } else {
          alert(data.error || "Registration failed. Please try again.");
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


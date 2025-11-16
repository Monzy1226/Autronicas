<?php
require_once __DIR__ . '/auth.php';
requireLogin();
checkSessionTimeout();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Autronicas Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
</head>
<body>
  <header class="header">
    <h1>Inventory Management System</h1>
    <nav class="nav">
      <a href="dashboard.php" class="active"><strong>Dashboard</strong></a>
      <a href="inventory.php"><strong>Inventory</strong></a>
      <a href="sales.php"><strong>Sales</strong></a>
      <a href="api/logout.php" id="logout"><strong>Logout</strong></a>
    </nav>
  </header>

  <main class="main-container fade-in">
    <section class="stats">
      <div class="card">
        <h3>Total Items</h3>
        <p id="total-items">0</p>
      </div>
      <div class="card">
        <h3>Total Value</h3>
        <p id="total-value">₱0.00</p>
      </div>
      <div class="card">
        <h3>Categories</h3>
        <p id="total-categories">0</p>
      </div>
      <div class="card">
        <h3>Low Stock</h3>
        <p id="low-stock">0</p>
      </div>
    </section>

    <section id="low-stock-alert" class="alert hidden">
      <h4>⚠️ Low Stock Alert</h4>
      <p id="low-stock-list"></p>
    </section>

    <section class="breakdown">
      <h2>Product Category Breakdown</h2>
      <table id="category-table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Current</th>
            <th>Minimum</th>
            <th>Total Value</th>
          </tr>
        </thead>
        <tbody>
          <!-- dynamically generated -->
        </tbody>
      </table>
    </section>
  </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const totalItemsEl = document.getElementById('total-items');
  const totalValueEl = document.getElementById('total-value');
  const totalCategoriesEl = document.getElementById('total-categories');
  const lowStockEl = document.getElementById('low-stock');
  const lowStockListEl = document.getElementById('low-stock-list');
  const lowStockAlert = document.getElementById('low-stock-alert');
  const categoryTableBody = document.querySelector('#category-table tbody');

  function updateDashboard() {
    fetch('api/dashboard.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const stats = data.data;
          
          totalItemsEl.textContent = stats.total_items;
          totalValueEl.textContent = "₱" + parseFloat(stats.total_value).toFixed(2);
          totalCategoriesEl.textContent = stats.total_categories;
          lowStockEl.textContent = stats.low_stock;

          // Low stock alert
          if (stats.low_stock > 0) {
            lowStockAlert.classList.remove('hidden');
            lowStockListEl.innerHTML = stats.low_stock_items
              .map(i => `<p style="color:red">${i.description} — ${i.quantity} left</p>`)
              .join('');
          } else {
            lowStockAlert.classList.add('hidden');
          }

          // Category breakdown
          categoryTableBody.innerHTML = '';
          stats.category_breakdown.forEach(cat => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${cat.category}</td>
              <td>${cat.current}</td>
              <td>${cat.minimum}</td>
              <td>₱${parseFloat(cat.value).toFixed(2)}</td>
            `;
            categoryTableBody.appendChild(tr);
          });
        } else {
          console.error('Error loading dashboard:', data.error);
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
  }

  // Handle logout
  document.getElementById('logout').addEventListener('click', function(e) {
    e.preventDefault();
    fetch('api/logout.php')
      .then(() => {
        window.location.href = 'index.php';
      });
  });

  // Refresh dashboard every 5 seconds
  setInterval(updateDashboard, 5000);
  updateDashboard();
});
</script>
</body>
</html>


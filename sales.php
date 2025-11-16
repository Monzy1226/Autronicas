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
  <title>Sales Management | Autronicas</title>
  <link rel="stylesheet" href="sales.css" />
</head>
<body>
<header class="navbar">
  <div class="logo">Inventory Management System</div>
  <nav>
    <a href="dashboard.php"><strong>Dashboard</strong></a>
    <a href="inventory.php"><strong>Inventory</strong></a>
    <a href="sales.php" class="active"><strong>Sales</strong></a>
    <a href="api/logout.php" id="logout"><strong>Logout</strong></a>
  </nav>
</header>

<main class="container fade-in">

  <div class="cards">
    <div class="card">
      <h4>Daily Summary Report</h4>
      <p>Total Sales: ₱<span id="dailyTotal">0.00</span></p>
    </div>

    <div class="card">
      <h4>View Sales by Date</h4>
      <input type="date" id="viewSalesDate" />
      <p>Showing sales for: <span id="selectedDate"></span></p>
      <p>Total Sales: ₱<span id="salesByDateTotal">0.00</span></p>
    </div>

    <div class="card">
      <h4>Sales by Date Range</h4>
      <label>Start: <input type="date" id="startDate" /></label>
      <label>End: <input type="date" id="endDate" /></label>
      <p>Total: ₱<span id="salesByRangeTotal">0.00</span></p>
    </div>
  </div>

  <div class="top-bar">
    <h2>Sales Management</h2>
    <button id="addSaleBtn" class="btn-add">＋ Add Job Order</button>
  </div>

  <h3>Sales Log</h3>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Job Order No.</th>
        <th>Vehicle / Plate No.</th>
        <th>Total Labor (₱)</th>
        <th>Total Parts Price (₱)</th>
        <th>Unit Price (₱)</th>
        <th>Total SRP (₱)</th>
        <th>Profit (₱)</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="salesTableBody"></tbody>
  </table>
</main>

<!-- Add/Edit Job Order Modal -->
<div id="saleModal" class="modal hidden">
  <div class="modal-content slide-up">
    <h2 id="modalTitle">Add Job Order</h2>
    <input type="hidden" id="saleId" />
    <input type="text" id="vehiclePlate" placeholder="Vehicle / Plate No." />
    <input type="number" id="laborTotal" placeholder="Total Labor (₱)" step="0.01" />
    <input type="number" id="partsTotal" placeholder="Total Parts Price (₱)" step="0.01" />
    <input type="number" id="unitPrice" placeholder="Unit Price / Cost (₱)" step="0.01" />
    <input type="number" id="srpTotal" placeholder="Total SRP (₱)" readonly />
    <button id="saveSale" class="btn-primary">Save</button>
    <button id="closeModal" class="btn-secondary">Cancel</button>
  </div>
</div>
<script>
let editingSaleId = null;
let allSales = [];

const salesTableBody = document.getElementById('salesTableBody');
const saleModal = document.getElementById('saleModal');
const saveSaleBtn = document.getElementById('saveSale');
const addSaleBtn = document.getElementById('addSaleBtn');
const closeModal = document.getElementById('closeModal');
const dailyTotalEl = document.getElementById('dailyTotal');

/* ===== RENDER SALES ===== */
function renderSales(filteredSales = null) {
  const data = filteredSales || allSales;
  salesTableBody.innerHTML = '';

  if (data.length === 0) {
    salesTableBody.innerHTML = '<tr><td colspan="10" style="text-align:center;">No sales found</td></tr>';
    dailyTotalEl.textContent = '0.00';
    return;
  }

  data.forEach((sale) => {
    const profit = parseFloat(sale.profit || (sale.srp_total - sale.unit_price));
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${sale.date}</td>
      <td>${sale.job_order_no}</td>
      <td>${sale.vehicle_plate}</td>
      <td>₱${parseFloat(sale.labor_total).toFixed(2)}</td>
      <td>₱${parseFloat(sale.parts_total).toFixed(2)}</td>
      <td>₱${parseFloat(sale.unit_price).toFixed(2)}</td>
      <td>₱${parseFloat(sale.srp_total).toFixed(2)}</td>
      <td>₱${profit.toFixed(2)}</td>
      <td>${sale.confirmed ? "✅ Confirmed" : "🕓 Pending"}</td>
      <td>
        ${!sale.confirmed ? `
          <button onclick="editSale(${sale.id})">Edit</button>
          <button onclick="confirmSale(${sale.id})">Confirm</button>
        ` : `<button disabled>Locked</button>`}
      </td>
    `;
    salesTableBody.appendChild(tr);
  });

  const total = data.reduce((sum, s) => sum + parseFloat(s.srp_total), 0);
  dailyTotalEl.textContent = total.toFixed(2);
}

/* ===== LOAD SALES ===== */
function loadSales() {
  fetch('api/sales.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        allSales = data.data;
        renderSales();
      } else {
        console.error('Error loading sales:', data.error);
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

/* ===== AUTO-CALCULATE SRP (LABOR + PARTS) ===== */
document.getElementById('laborTotal').addEventListener('input', updateSRP);
document.getElementById('partsTotal').addEventListener('input', updateSRP);

function updateSRP() {
  const labor = parseFloat(document.getElementById('laborTotal').value) || 0;
  const parts = parseFloat(document.getElementById('partsTotal').value) || 0;
  document.getElementById('srpTotal').value = (labor + parts).toFixed(2);
}

/* ===== ADD SALE ===== */
addSaleBtn.onclick = () => {
  editingSaleId = null;
  document.getElementById('saleId').value = '';
  document.getElementById('modalTitle').textContent = "Add Job Order";
  ['vehiclePlate', 'laborTotal', 'partsTotal', 'unitPrice', 'srpTotal'].forEach(id => document.getElementById(id).value = '');
  saveSaleBtn.textContent = "Save";
  saleModal.classList.remove('hidden');
};

closeModal.onclick = () => {
  saleModal.classList.add('hidden');
  editingSaleId = null;
  document.getElementById('saleId').value = '';
};

/* ===== SAVE SALE ===== */
saveSaleBtn.onclick = () => {
  const vehiclePlate = document.getElementById('vehiclePlate').value.trim();
  const laborTotal = parseFloat(document.getElementById('laborTotal').value) || 0;
  const partsTotal = parseFloat(document.getElementById('partsTotal').value) || 0;
  const unitPrice = parseFloat(document.getElementById('unitPrice').value) || 0;
  const srpTotal = laborTotal + partsTotal;

  if (!vehiclePlate) {
    alert("Please enter the Vehicle/Plate No.");
    return;
  }

  const saleData = {
    vehiclePlate,
    laborTotal,
    partsTotal,
    unitPrice,
    date: new Date().toISOString().split('T')[0]
  };

  const url = 'api/sales.php';
  const method = editingSaleId ? 'PUT' : 'POST';

  if (editingSaleId) {
    saleData.id = editingSaleId;
  }

  fetch(url, {
    method: method,
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(saleData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      saleModal.classList.add('hidden');
      editingSaleId = null;
      document.getElementById('saleId').value = '';
      loadSales();
    } else {
      alert(data.error || "An error occurred. Please try again.");
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert("An error occurred. Please try again.");
  });
};

/* ===== EDIT SALE ===== */
function editSale(id) {
  const sale = allSales.find(s => s.id == id);
  if (!sale) return;
  if (sale.confirmed) {
    alert("Confirmed job orders cannot be edited.");
    return;
  }

  editingSaleId = sale.id;
  document.getElementById('saleId').value = sale.id;
  document.getElementById('modalTitle').textContent = "Edit Job Order";
  document.getElementById('vehiclePlate').value = sale.vehicle_plate;
  document.getElementById('laborTotal').value = sale.labor_total;
  document.getElementById('partsTotal').value = sale.parts_total;
  document.getElementById('unitPrice').value = sale.unit_price;
  document.getElementById('srpTotal').value = sale.srp_total;
  saleModal.classList.remove('hidden');
}

/* ===== CONFIRM SALE ===== */
function confirmSale(id) {
  if (confirm("Are you sure you want to confirm this Job Order? Once confirmed, it cannot be edited.")) {
    fetch('api/sales.php', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadSales();
      } else {
        alert(data.error || "An error occurred. Please try again.");
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert("An error occurred. Please try again.");
    });
  }
}

/* ===== FILTER SALES ===== */
document.getElementById('viewSalesDate').onchange = (e) => {
  const date = e.target.value;
  if (!date) {
    renderSales();
    return;
  }
  document.getElementById('selectedDate').textContent = date;
  fetch(`api/sales.php?date=${date}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        renderSales(data.data);
        const total = data.data.reduce((sum, s) => sum + parseFloat(s.srp_total), 0);
        document.getElementById('salesByDateTotal').textContent = total.toFixed(2);
      }
    });
};

document.getElementById('startDate').onchange = calculateRange;
document.getElementById('endDate').onchange = calculateRange;

function calculateRange() {
  const start = document.getElementById('startDate').value;
  const end = document.getElementById('endDate').value;
  if (!start || !end) {
    renderSales();
    return;
  }
  fetch(`api/sales.php?start_date=${start}&end_date=${end}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        renderSales(data.data);
        const total = data.data.reduce((sum, s) => sum + parseFloat(s.srp_total), 0);
        document.getElementById('salesByRangeTotal').textContent = total.toFixed(2);
      }
    });
}

/* ===== HANDLE LOGOUT ===== */
document.getElementById('logout').addEventListener('click', function(e) {
  e.preventDefault();
  fetch('api/logout.php')
    .then(() => {
      window.location.href = 'index.php';
    });
});

/* ===== INITIAL LOAD ===== */
loadSales();
</script>
</body>
</html>


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
  <title>Job Order - Autronicas</title>
  <link rel="stylesheet" href="job-order.css" />
</head>
<body>
  <!-- SELECTION SECTION -->
  <section id="choose-type" style="text-align:center; margin-bottom: 20px;">
    <h2>Select Job Order Type</h2>
    <button id="private-btn" class="btn-primary" style="padding:10px 25px; margin:5px;">Private</button>
    <button id="lgu-btn" class="btn-secondary" style="padding:10px 25px; margin:5px;">LGU</button>
  </section>

  <!-- JOB ORDER FORM -->
  <div class="job-order-container hidden" id="jobOrderContent">
    <div class="header">
      <img src="Logo.png" alt="Company Logo" class="logo" />
      <div class="company-info">
        <h1>AUTRONICAS</h1>
        <h2>AUTO SERVICE AND SPARE PARTS CORP.</h2>
        <p>
          Maharlika Highway Sitio Bagong Tulay, Brgy. Bukal, Pagbilao, Quezon<br />
          Contact Info: (042) 785-0428 | 0998-999-0252<br />
          Email: autronicas.official@gmail.com
        </p>
      </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
      <h3 id="jobOrderTitle" style="margin:0;">JOB ORDER (Private)</h3>
      <h4 style="margin:0; margin-right:30px;">Job Order No: <span id="jobOrderNumber">1</span></h4>
    </div>

    <hr class="divider">

    <div class="job-info">
      <div class="left">
        <label><strong>Name / Company:</strong></label>
        <input type="text" id="customerName" class="input-line" placeholder="Enter name or company" />
        <br><br>
        <label><strong>Address:</strong></label>
        <input type="text" id="address" class="input-line" placeholder="Enter address" />
      </div>
      <div class="right">
        <label><strong>Contact No.:</strong></label>
        <input type="text" id="contactNo" class="input-line" placeholder="Enter contact number" />
      </div>
    </div>

    <table class="details-table">
      <tr>
        <td><strong>Model:</strong> <input type="text" id="model"></td>
        <td><strong>Plate No.:</strong> <input type="text" id="plateNo"></td>
      </tr>
      <tr>
        <td><strong>Motor Chasis:</strong> <input type="text" id="motorChasis"></td>
        <td><strong>Time In:</strong> <input type="text" id="timeIn"></td>
        <td><strong>Date:</strong> <input type="date" id="orderDate"></td>
      </tr>
      <tr>
        <td><strong>Vehicle Color:</strong> <input type="text" id="vehicleColor"></td>
        <td><strong>Fuel Level:</strong> <input type="text" id="fuelLevel"></td>
        <td><strong>Engine Number:</strong> <input type="text" id="engineNumber"></td>
      </tr>
    </table>

    <!-- LABOR SECTION -->
    <table class="job-table">
      <tr class="section-header">
        <th colspan="5">
          <div class="section-header-content">
            <span class="section-title-text">LABOR</span>
            <button class="add-btn" onclick="addRow('laborRows')">+ Add Row</button>
          </div>
        </th>
      </tr>
      <tr>
        <th>JOB DESCRIPTION</th>
        <th>QTY</th>
        <th>UNIT</th>
        <th>UNIT PRICE</th>
        <th>AMOUNT</th>
      </tr>
      <tbody id="laborRows">
        <tr>
          <td><input type="text" placeholder="Enter labor details..." /></td>
          <td><input type="number" min="1" placeholder="0" oninput="updateTotal()" /></td>
          <td><input type="text" placeholder="pcs/hr" /></td>
          <td><input type="number" min="0" placeholder="0.00" step="0.01" oninput="updateTotal()" /></td>
          <td><input type="number" min="0" placeholder="0.00" readonly /></td>
        </tr>
      </tbody>
    </table>

    <!-- PARTS SECTION -->
    <table class="job-table">
      <tr class="section-header">
        <th colspan="5">
          <div class="section-header-content">
            <span class="section-title-text">PARTS</span>
            <button class="add-btn" onclick="addRow('partsRows')">+ Add Row</button>
          </div>
        </th>
      </tr>
      <tr>
        <th>JOB DESCRIPTION</th>
        <th>QTY</th>
        <th>UNIT</th>
        <th>UNIT PRICE</th>
        <th>AMOUNT</th>
      </tr>
      <tbody id="partsRows">
        <tr>
          <td><input type="text" placeholder="Enter part details..." /></td>
          <td><input type="number" min="1" placeholder="0" oninput="updateTotal()" /></td>
          <td><input type="text" placeholder="pcs" /></td>
          <td><input type="number" min="0" placeholder="0.00" step="0.01" oninput="updateTotal()" /></td>
          <td><input type="number" min="0" placeholder="0.00" readonly /></td>
        </tr>
      </tbody>
    </table>

    <!-- TOTAL -->
    <div style="text-align:right; margin-top:5px;">
      <strong>TOTAL AMOUNT: </strong><span id="totalAmount"><strong>₱0.00</strong></span>
    </div>

    <p class="note">I AGREE THAT ALL WORK HAS BEEN PERFORMED TO MY SATISFACTION.</p>

    <div class="signatures">
      <div>Mechanic/Technician</div>

      <div>Mrs. Herminia Baracael<br /><small>Chief of Operation Officer</small></div>
    </div>

    <div class="received-section">
      <p>Received by: ___________________________</p>
      <p>(Signature over printed name)</p>
    </div>
  </div>

  <div class="actions" style="text-align:center; margin-top:20px;">
    <button onclick="printJobOrder()">🖨 Print Job Order</button>
  </div>

  <script>
let jobOrderCounter = 1;
let currentType = "";
let currentDate = new Date().toISOString().split("T")[0];
let savedOrders = [];
let editingOrderId = null;

// === Get Next Job Order Number ===
async function getNextJobOrderNo() {
  try {
    const response = await fetch('api/sales.php');
    const data = await response.json();
    if (data.success && data.data.length > 0) {
      const maxNo = Math.max(...data.data.map(s => s.job_order_no));
      return maxNo + 1;
    }
    return 1;
  } catch (error) {
    console.error('Error getting next job order number:', error);
    return 1;
  }
}

// === Add Row (Specific Section) ===
function addRow(sectionId) {
  const tbody = document.getElementById(sectionId);
  const row = document.createElement("tr");
  row.innerHTML = `
    <td><input type="text" placeholder="Enter details..." /></td>
    <td><input type="number" min="1" placeholder="0" oninput="updateTotal()" /></td>
    <td><input type="text" placeholder="pcs/hr" /></td>
    <td><input type="number" min="0" placeholder="0.00" step="0.01" oninput="updateTotal()" /></td>
    <td><input type="number" min="0" placeholder="0.00" readonly /></td>
  `;
  tbody.appendChild(row);
  updateTotal();
}

// === Update Total Amount for All Rows ===
function updateTotal() {
  let total = 0;

  ["laborRows", "partsRows"].forEach(id => {
    const rows = document.querySelectorAll(`#${id} tr`);
    rows.forEach(row => {
      const qtyInput = row.querySelector('td:nth-child(2) input');
      const unitPriceInput = row.querySelector('td:nth-child(4) input');
      const amountInput = row.querySelector('td:nth-child(5) input');

      const qty = parseFloat(qtyInput?.value) || 0;
      const price = parseFloat(unitPriceInput?.value) || 0;
      const amount = qty * price;

      if (amountInput) {
        amountInput.value = amount.toFixed(2);
      }

      total += amount;
    });
  });

  document.getElementById("totalAmount").innerHTML = `<strong>₱${total.toFixed(2)}</strong>`;
}

// === Type Selection ===
document.getElementById("private-btn").addEventListener("click", async function() {
  currentType = "Private";
  jobOrderCounter = await getNextJobOrderNo();
  showJobOrderForm();
});

document.getElementById("lgu-btn").addEventListener("click", async function() {
  currentType = "LGU";
  jobOrderCounter = await getNextJobOrderNo();
  showJobOrderForm();
});

// === Show Job Order Form ===
function showJobOrderForm() {
  document.getElementById("choose-type").style.display = "none";
  document.getElementById("jobOrderContent").classList.remove("hidden");
  document.getElementById("jobOrderTitle").innerText = `JOB ORDER (${currentType})`;
  document.getElementById("jobOrderNumber").innerText = jobOrderCounter;
  currentDate = document.getElementById("orderDate").value || new Date().toISOString().split("T")[0];
  if (!document.getElementById("orderDate").value) {
    document.getElementById("orderDate").value = currentDate;
  }

  // Create header nav bar
  let headerBar = document.getElementById("headerButtons");
  if (!headerBar) {
    headerBar = document.createElement("div");
    headerBar.id = "headerButtons";
    headerBar.style.display = "flex";
    headerBar.style.justifyContent = "center";
    headerBar.style.alignItems = "center";
    headerBar.style.gap = "8px";
    headerBar.style.marginBottom = "10px";

    headerBar.innerHTML = `
      <button id="backBtn" class="nav-btn" onclick="goBack()">←</button>
      <button id="saveBtn" class="nav-btn" onclick="saveJobOrder()">💾 Save</button>
      <button id="nextBtn" class="nav-btn" onclick="nextJobOrder()">→</button>
      <select id="jobIndicator" class="indicator" onchange="loadSelectedOrder(this.value)"></select>
    `;
    document.getElementById("choose-type").insertAdjacentElement("afterend", headerBar);
  }

  loadSavedOrders();
  updateIndicator();
  headerBar.style.display = "flex";
}

// === Load Saved Orders ===
async function loadSavedOrders() {
  try {
    const response = await fetch('api/job_orders.php');
    const data = await response.json();
    if (data.success) {
      savedOrders = data.data;
      updateIndicator();
    }
  } catch (error) {
    console.error('Error loading saved orders:', error);
  }
}

// === Update Dropdown Indicator ===
function updateIndicator() {
  const indicator = document.getElementById("jobIndicator");
  if (!indicator) return;
  
  indicator.innerHTML = "";

  savedOrders.forEach((order, index) => {
    if (order.job_order_no == jobOrderCounter && order.type === currentType) {
      const opt = document.createElement("option");
      opt.value = order.id;
      opt.text = `${order.type} #${order.job_order_no} — ${order.date}`;
      opt.selected = true;
      indicator.appendChild(opt);
      editingOrderId = order.id;
    } else {
      const opt = document.createElement("option");
      opt.value = order.id;
      opt.text = `${order.type} #${order.job_order_no} — ${order.date}`;
      indicator.appendChild(opt);
    }
  });

  const currentOpt = document.createElement("option");
  currentOpt.text = `${currentType} #${jobOrderCounter} — ${currentDate}`;
  currentOpt.value = "current";
  if (savedOrders.filter(o => o.job_order_no == jobOrderCounter && o.type === currentType).length === 0) {
    currentOpt.selected = true;
    editingOrderId = null;
  }
  indicator.appendChild(currentOpt);

  document.getElementById("jobOrderNumber").innerText = jobOrderCounter;
}

// === Save Job Order ===
async function saveJobOrder() {
  currentDate = document.getElementById("orderDate").value || new Date().toISOString().split("T")[0];

  const jobData = {
    job_order_no: jobOrderCounter,
    type: currentType,
    date: currentDate,
    customer_name: document.getElementById("customerName").value.trim(),
    address: document.getElementById("address").value.trim(),
    contact_no: document.getElementById("contactNo").value.trim(),
    model: document.getElementById("model").value.trim(),
    plate_no: document.getElementById("plateNo").value.trim(),
    motor_chasis: document.getElementById("motorChasis").value.trim(),
    time_in: document.getElementById("timeIn").value.trim(),
    vehicle_color: document.getElementById("vehicleColor").value.trim(),
    fuel_level: document.getElementById("fuelLevel").value.trim(),
    engine_number: document.getElementById("engineNumber").value.trim(),
    labor: collectTableData("laborRows"),
    parts: collectTableData("partsRows"),
    total_amount: parseFloat(document.getElementById("totalAmount").textContent.replace(/[₱,]/g, '')) || 0
  };

  if (editingOrderId) {
    jobData.id = editingOrderId;
  }

  try {
    const response = await fetch('api/job_orders.php', {
      method: editingOrderId ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(jobData)
    });

    const data = await response.json();
    if (data.success) {
      alert(editingOrderId ? `💾 Updated: ${currentType} #${jobOrderCounter}` : `💾 Saved: ${currentType} #${jobOrderCounter}`);
      jobOrderCounter = await getNextJobOrderNo();
      clearInputs();
      loadSavedOrders();
    } else {
      alert(data.error || "An error occurred. Please try again.");
    }
  } catch (error) {
    console.error('Error saving job order:', error);
    alert("An error occurred. Please try again.");
  }
}

// === Collect labor/parts tables ===
function collectTableData(sectionId) {
  const rows = [];
  document.querySelectorAll(`#${sectionId} tr`).forEach(row => {
    const inputs = row.querySelectorAll("input");
    if (inputs.length > 0) {
      rows.push({
        desc: inputs[0].value,
        qty: inputs[1].value,
        unit: inputs[2].value,
        price: inputs[3].value,
        amount: inputs[4].value
      });
    }
  });
  return rows;
}

// === Load job order by ID ===
async function loadSelectedOrder(id) {
  if (id === "current") {
    editingOrderId = null;
    return;
  }

  try {
    const response = await fetch(`api/job_orders.php?id=${id}`);
    const data = await response.json();
    if (data.success && data.data.length > 0) {
      const order = data.data[0];
      if (!order) return;

      currentType = order.type;
      jobOrderCounter = order.job_order_no;
      editingOrderId = order.id;

      document.getElementById("jobOrderTitle").innerText = `JOB ORDER (${currentType})`;
      document.getElementById("jobOrderNumber").innerText = jobOrderCounter;
      document.getElementById("customerName").value = order.customer_name || "";
      document.getElementById("address").value = order.address || "";
      document.getElementById("contactNo").value = order.contact_no || "";
      document.getElementById("model").value = order.model || "";
      document.getElementById("plateNo").value = order.plate_no || "";
      document.getElementById("motorChasis").value = order.motor_chasis || "";
      document.getElementById("timeIn").value = order.time_in || "";
      document.getElementById("orderDate").value = order.date || "";
      document.getElementById("vehicleColor").value = order.vehicle_color || "";
      document.getElementById("fuelLevel").value = order.fuel_level || "";
      document.getElementById("engineNumber").value = order.engine_number || "";

      loadTable("laborRows", order.labor_data || []);
      loadTable("partsRows", order.parts_data || []);

      document.getElementById("totalAmount").innerHTML = `<strong>₱${parseFloat(order.total_amount || 0).toFixed(2)}</strong>`;
      updateIndicator();
    }
  } catch (error) {
    console.error('Error loading job order:', error);
  }
}

// === Load table data ===
function loadTable(sectionId, data) {
  const tbody = document.getElementById(sectionId);
  tbody.innerHTML = "";

  if (data.length === 0) {
    addRow(sectionId);
    return;
  }

  data.forEach(row => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><input type="text" value="${row.desc || ''}" /></td>
      <td><input type="number" value="${row.qty || ''}" oninput="updateTotal()" /></td>
      <td><input type="text" value="${row.unit || ''}" /></td>
      <td><input type="number" value="${row.price || ''}" step="0.01" oninput="updateTotal()" /></td>
      <td><input type="number" value="${row.amount || ''}" readonly /></td>
    `;
    tbody.appendChild(tr);
  });
  updateTotal();
}

// === Back Button ===
async function goBack() {
  if (jobOrderCounter > 1) {
    jobOrderCounter--;
    await loadJobIfExists();
  } else {
    document.getElementById("jobOrderContent").classList.add("hidden");
    document.getElementById("choose-type").style.display = "block";
    const headerButtons = document.getElementById("headerButtons");
    if (headerButtons) headerButtons.style.display = "none";
  }
}

// === Next Button ===
async function nextJobOrder() {
  jobOrderCounter = await getNextJobOrderNo();
  clearInputs();
  editingOrderId = null;
  loadSavedOrders();
  alert(`➡️ New Job Order #${jobOrderCounter}`);
}

// === Load existing job order if it exists ===
async function loadJobIfExists() {
  await loadSavedOrders();
  const existing = savedOrders.find(
    o => o.type === currentType && o.job_order_no == jobOrderCounter
  );
  if (existing) {
    await loadSelectedOrder(existing.id);
  } else {
    clearInputs();
  }
}

// === Clear all fields ===
function clearInputs() {
  document.getElementById("customerName").value = "";
  document.getElementById("address").value = "";
  document.getElementById("contactNo").value = "";
  document.getElementById("model").value = "";
  document.getElementById("plateNo").value = "";
  document.getElementById("motorChasis").value = "";
  document.getElementById("timeIn").value = "";
  document.getElementById("orderDate").value = new Date().toISOString().split("T")[0];
  document.getElementById("vehicleColor").value = "";
  document.getElementById("fuelLevel").value = "";
  document.getElementById("engineNumber").value = "";
  document.getElementById("totalAmount").innerHTML = "<strong>₱0.00</strong>";
  document.getElementById("laborRows").innerHTML = "";
  document.getElementById("partsRows").innerHTML = "";
  addRow("laborRows");
  addRow("partsRows");
  editingOrderId = null;
}

// === Print Job Order ===
function printJobOrder() {
  const chooseSection = document.getElementById("choose-type");
  const headerButtons = document.getElementById("headerButtons");

  if (chooseSection) chooseSection.style.display = "none";
  if (headerButtons) headerButtons.style.display = "none";

  window.print();

  if (headerButtons) headerButtons.style.display = "flex";
}
</script>
</body>
</html>


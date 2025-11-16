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
  <title>Inventory Management | Autronicas</title>
  <link rel="stylesheet" href="inventory.css" />
</head>
<body>
  <header class="header">
    <h1>Inventory Management System</h1>
    <nav class="nav">
      <a href="dashboard.php"><strong>Dashboard</strong></a>
      <a href="inventory.php" class="active"><strong>Inventory</strong></a>
      <a href="sales.php"><strong>Sales</strong></a>
      <a href="api/logout.php" id="logout"><strong>Logout</strong></a>
    </nav>
  </header>

  <main class="main-container">
    <section id="inventory-section">
      <section class="inventory-header">
        <h2 id="inventory-title">Inventory Management</h2>
        <p>Manage your inventory items, track stock levels, and monitor performance.</p>
        <div class="inventory-actions">
          <button id="add-item-btn">+ Add Item</button>
        </div>
      </section>

      <!-- Popup overlay -->
      <div id="overlay"></div>

      <!-- Popup Add Form -->
      <div id="inline-form">
        <form id="add-item-form">
          <h3 id="form-title">Add Item</h3>
          <input type="hidden" id="item-id" />
          <input type="text" id="code" placeholder="Product Code" required />
          <input type="text" id="description" placeholder="Product Description" required />
          <input type="text" id="category" placeholder="Category" required />
          <input type="number" id="quantity" placeholder="Quantity" required />
          <input type="number" id="minQuantity" placeholder="Minimum Quantity" required />
          <input type="number" id="unitPrice" placeholder="Unit Price" required step="0.01" />
          <input type="text" id="srpPrivate" placeholder="Private SRP (Auto)" readonly />
          <input type="text" id="srpLGU" placeholder="LGU SRP (Auto)" readonly />
          <div style="margin-top:10px;">
            <button type="submit" id="save-btn">Save</button>
            <button type="button" id="cancel-btn">Cancel</button>
          </div>
        </form>
      </div>

      <table border="1" cellpadding="5" cellspacing="0" style="margin-top:10px; width:100%;">
        <thead>
          <tr>
            <th>Product Code</th>
            <th>Product Description</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Minimum</th>
            <th>Unit Price</th>
            <th>Private SRP</th>
            <th>LGU SRP</th>
            <th>Total Value</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="inventory-body"></tbody>
      </table>
    </section>
  </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const addBtn = document.getElementById('add-item-btn');
  const inlineForm = document.getElementById('inline-form');
  const addForm = document.getElementById('add-item-form');
  const cancelBtn = document.getElementById('cancel-btn');
  const overlay = document.getElementById('overlay');
  const tableBody = document.getElementById('inventory-body');
  const unitPriceInput = document.getElementById('unitPrice');
  const srpPrivateInput = document.getElementById('srpPrivate');
  const srpLGUInput = document.getElementById('srpLGU');
  const formTitle = document.getElementById('form-title');
  const itemIdInput = document.getElementById('item-id');
  let editId = null;

  renderInventory();

  // Auto-calculate SRPs when unit price changes
  unitPriceInput.addEventListener('input', updateSRP);

  function updateSRP() {
    const unitPrice = parseFloat(unitPriceInput.value);
    if (!isNaN(unitPrice)) {
      // Private = +25%, LGU = +60%
      const srpPrivate = Math.ceil((unitPrice * 1.25) / 10) * 10;
      const srpLGU = Math.ceil((unitPrice * 1.6) / 10) * 10;

      srpPrivateInput.value = srpPrivate.toFixed(2);
      srpLGUInput.value = srpLGU.toFixed(2);
    } else {
      srpPrivateInput.value = "";
      srpLGUInput.value = "";
    }
  }

  // Show popup form
  addBtn.addEventListener('click', () => {
    editId = null;
    itemIdInput.value = '';
    formTitle.textContent = 'Add Item';
    addForm.reset();
    inlineForm.style.display = 'block';
    overlay.style.display = 'block';
  });

  // Hide popup
  cancelBtn.addEventListener('click', closePopup);
  overlay.addEventListener('click', closePopup);

  function closePopup() {
    inlineForm.style.display = 'none';
    overlay.style.display = 'none';
    addForm.reset();
    editId = null;
    itemIdInput.value = '';
    formTitle.textContent = 'Add Item';
  }

  // Add / Edit item
  addForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const itemData = {
      code: document.getElementById('code').value.trim(),
      description: document.getElementById('description').value.trim(),
      category: document.getElementById('category').value.trim(),
      quantity: parseInt(document.getElementById('quantity').value),
      minQuantity: parseInt(document.getElementById('minQuantity').value),
      unitPrice: parseFloat(unitPriceInput.value),
      srpPrivate: parseFloat(srpPrivateInput.value),
      srpLGU: parseFloat(srpLGUInput.value)
    };

    const url = 'api/inventory.php';
    const method = editId ? 'PUT' : 'POST';
    
    if (editId) {
      itemData.id = editId;
    }

    fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(itemData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(editId ? 'Item updated successfully!' : 'Item added successfully!');
        renderInventory();
        closePopup();
      } else {
        alert(data.error || 'An error occurred. Please try again.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred. Please try again.');
    });
  });

  // Render table
  function renderInventory() {
    fetch('api/inventory.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          tableBody.innerHTML = '';
          data.data.forEach((item) => {
            const tr = document.createElement('tr');
            const totalValue = parseFloat(item.quantity) * parseFloat(item.unit_price);
            const isLowStock = parseFloat(item.quantity) <= parseFloat(item.min_quantity);
            
            tr.innerHTML = `
              <td>${item.code}</td>
              <td>${item.description}</td>
              <td>${item.category}</td>
              <td>${item.quantity}</td>
              <td>${item.min_quantity}</td>
              <td>₱${parseFloat(item.unit_price).toFixed(2)}</td>
              <td>₱${parseFloat(item.srp_private).toFixed(2)}</td>
              <td>₱${parseFloat(item.srp_lgu).toFixed(2)}</td>
              <td>₱${totalValue.toFixed(2)}</td>
              <td>${isLowStock ? "<span style='color:red;'>Low</span>" : "In Stock"}</td>
              <td style="text-align:center;">
                <button class="edit" data-id="${item.id}">Edit</button>
                <button class="delete" data-id="${item.id}">Delete</button>
              </td>
            `;
            tableBody.appendChild(tr);
          });
        } else {
          console.error('Error loading inventory:', data.error);
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
  }

  // Edit / Delete
  tableBody.addEventListener('click', (e) => {
    if (e.target.classList.contains('edit')) {
      const id = parseInt(e.target.dataset.id);
      fetch('api/inventory.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const item = data.data.find(i => i.id == id);
            if (item) {
              editId = item.id;
              itemIdInput.value = item.id;
              formTitle.textContent = 'Edit Item';
              document.getElementById('code').value = item.code;
              document.getElementById('description').value = item.description;
              document.getElementById('category').value = item.category;
              document.getElementById('quantity').value = item.quantity;
              document.getElementById('minQuantity').value = item.min_quantity;
              document.getElementById('unitPrice').value = item.unit_price;
              document.getElementById('srpPrivate').value = parseFloat(item.srp_private).toFixed(2);
              document.getElementById('srpLGU').value = parseFloat(item.srp_lgu).toFixed(2);
              inlineForm.style.display = 'block';
              overlay.style.display = 'block';
            }
          }
        });
    }

    if (e.target.classList.contains('delete')) {
      const id = parseInt(e.target.dataset.id);
      if (confirm('Are you sure you want to delete this item?')) {
        fetch('api/inventory.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Item deleted successfully!');
            renderInventory();
          } else {
            alert(data.error || 'An error occurred. Please try again.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
      }
    }
  });

  // Handle logout
  document.getElementById('logout').addEventListener('click', function(e) {
    e.preventDefault();
    fetch('api/logout.php')
      .then(() => {
        window.location.href = 'index.php';
      });
  });
});
</script>
</body>
</html>


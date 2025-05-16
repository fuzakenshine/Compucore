<?php
session_start();
include 'db_connect.php';

$admin_id = $_SESSION['user_id'];
$adminQuery = $conn->prepare("SELECT CONCAT(F_NAME, ' ', L_NAME) as full_name FROM users WHERE PK_USER_ID = ?");
$adminQuery->bind_param("i", $admin_id);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$adminName = $adminResult->num_rows > 0 ? $adminResult->fetch_assoc()['full_name'] : 'Test Admin';

// Get date range from request
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Prepare the base query
$query = "SELECT o.*, c.F_NAME, c.L_NAME, c.EMAIL, c.PHONE_NUM,
          GROUP_CONCAT(p.PROD_NAME SEPARATOR ', ') as products,
          SUM(od.QTY * od.PRICE) as total_amount
          FROM orders o 
          JOIN customer c ON o.FK1_CUSTOMER_ID = c.PK_CUSTOMER_ID 
          LEFT JOIN order_detail od ON o.PK_ORDER_ID = od.FK2_ORDER_ID
          LEFT JOIN products p ON od.FK1_PRODUCT_ID = p.PK_PRODUCT_ID
          WHERE o.ORDER_DATE BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";

// Add status filter if not 'all'
if ($status !== 'all') {
    $query .= " AND o.STATUS = ?";
}

$query .= " GROUP BY o.PK_ORDER_ID, c.F_NAME, c.L_NAME, c.EMAIL, c.PHONE_NUM
            ORDER BY o.ORDER_DATE DESC";

$stmt = $conn->prepare($query);

if ($status !== 'all') {
    $stmt->bind_param("sss", $start_date, $end_date, $status);
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            width: 200px;
            background-color: #d32f2f;
            height: 100vh;
            position: fixed;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .admin-profile {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }

        .admin-profile h3 {
            margin: 0;
            font-size: 1.2em;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: rgba(255,255,255,0.2);
        }

        .main-content {
            margin-left: 250px;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filter-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .filter-group {
            flex: 1;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #d32f2f;
            color: white;
        }

        .btn-primary:hover {
            background-color: #b71c1c;
        }

        .btn-secondary {
            background-color: #fff;
            color: #666;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background-color: #f5f5f5;
        }

        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-approved {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-rejected {
            background-color: #ffebee;
            color: #c62828;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .download-btn {
            background-color: #d32f2f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            background-color: #b71c1c;
        }

        .download-btn i {
            font-size: 16px;
        }

        .download-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-profile">
            <h3><?= htmlspecialchars($adminName) ?></h3>
        </div>
        <a href="Admin_home.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="Admin_orders.php">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        <a href="Admin_suppliers.php">
            <i class="fas fa-truck"></i> Suppliers
        </a>
        <a href="Admin_product.php">
            <i class="fas fa-box"></i> Products
        </a>
        <a href="Admin_customers.php">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="Admin_reports.php" class="active">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Detailed Reports</h2>
        </div>

        <div class="filters">
            <form id="filterForm" method="GET">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= $start_date ?>">
                    </div>
                    <div class="filter-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?= $end_date ?>">
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                            <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button type="button" class="download-btn" onclick="downloadPDF()" id="downloadBtn">
                        <i class="fas fa-download"></i> Download Report
                    </button>
                </div>
            </form>
        </div>

        <table id="reportsTable">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Products</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $row['PK_ORDER_ID'] ?></td>
                    <td><?= htmlspecialchars($row['F_NAME'] . ' ' . $row['L_NAME']) ?></td>
                    <td>
                        <div><?= htmlspecialchars($row['EMAIL']) ?></div>
                        <div><?= htmlspecialchars($row['PHONE_NUM']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($row['products']) ?></td>
                    <td style="text-align: right;">₱<?= str_replace('+', '', number_format($row['total_amount'] ?? 0, 2)) ?></td>
                    <td>
                        <span class="status-badge status-<?= strtolower($row['STATUS']) ?>">
                            <?= ucfirst($row['STATUS']) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['ORDER_DATE'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Helper to fetch image as base64
        async function getBase64ImageFromURL(url) {
            const res = await fetch(url);
            const blob = await res.blob();
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        }

        async function downloadPDF() {
            try {
                const downloadBtn = document.getElementById('downloadBtn');
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';

                // Show loading alert
                Swal.fire({
                    title: 'Generating PDF',
                    html: 'Please wait while we prepare your report...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Insert this before the title
                const logoBase64 = await getBase64ImageFromURL('./uploads/LOGO.PNG');
                const imgWidth = 35;
                const imgHeight = 20;
                const pageWidth = doc.internal.pageSize.getWidth();
                const imgX = (pageWidth - imgWidth) / 2;
                const logoY = 12; // Lowered margin
                doc.addImage(logoBase64, 'PNG', imgX, logoY, imgWidth, imgHeight);

                // Title below logo
                const titleY = logoY + imgHeight + 6;
                doc.setFontSize(18);
                doc.setTextColor(211, 47, 47);
                doc.setFont(undefined, 'bold');
                doc.text('Order Reports', pageWidth / 2, titleY, { align: 'center' });

                // Decorative line under title
                doc.setDrawColor(211, 47, 47);
                doc.setLineWidth(1);
                doc.line(pageWidth / 2 - 25, titleY + 3, pageWidth / 2 + 25, titleY + 3);

                // Date range and status
                doc.setFontSize(10);
                doc.setTextColor(0, 0, 0);
                doc.setFont(undefined, 'normal');
                doc.text(
                    `Date Range: ${document.querySelector('input[name=\"start_date\"]').value} to ${document.querySelector('input[name=\"end_date\"]').value}`,
                    pageWidth / 2,
                    titleY + 10,
                    { align: 'center' }
                );
                const status = document.querySelector('select[name=\"status\"]').value;
                doc.text(
                    `Status: ${status === 'all' ? 'All' : status.charAt(0).toUpperCase() + status.slice(1)}`,
                    pageWidth / 2,
                    titleY + 16,
                    { align: 'center' }
                );

                // Table startY
                const tableStartY = titleY + 22;

                // Get table data
                const table = document.getElementById('reportsTable');
                const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                    return Array.from(row.cells).map((cell, i) => {
                        let text = cell.textContent.trim();
                        // For the 'Total Amount' column, remove any leading peso or plus sign for PDF export
                        if (i === 4) {
                            text = text.replace('₱', '').replace(/^\+/, '');
                        }
                        return text;
                    });
                });
                
                // Add table with improved styling
                doc.autoTable({
                    head: [['Order ID', 'Customer', 'Contact', 'Products', 'Total Amount', 'Status', 'Date']],
                    body: rows,
                    startY: tableStartY,
                    theme: 'grid',
                    styles: {
                        fontSize: 8,
                        cellPadding: 1.5,
                        lineColor: [211, 47, 47],
                        lineWidth: 0.1
                    },
                    headStyles: {
                        fillColor: [211, 47, 47],
                        textColor: [255, 255, 255],
                        fontStyle: 'bold'
                    },
                    alternateRowStyles: {
                        fillColor: [245, 245, 245]
                    },
                    margin: { top: 10 },
                    columnStyles: {
                        4: { halign: 'right' } // Right-align the 'Total Amount' column
                    }
                });
                
                // Add footer with page numbers
                const pageCount = doc.internal.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.setTextColor(128, 128, 128);
                    doc.text(
                        `Page ${i} of ${pageCount}`,
                        doc.internal.pageSize.getWidth() / 2,
                        doc.internal.pageSize.getHeight() - 10,
                        { align: 'center' }
                    );
                }
                
                // Save the PDF
                doc.save('order-reports.pdf');

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'PDF Generated',
                    text: 'Your report has been downloaded successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error('Error generating PDF:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate PDF. Please try again.'
                });
            } finally {
                // Reset button state
                const downloadBtn = document.getElementById('downloadBtn');
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download Report';
            }
        }

        // Add event listener for form submission
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const startDate = new Date(this.start_date.value);
            const endDate = new Date(this.end_date.value);
            
            if (startDate > endDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'Start date cannot be later than end date'
                });
                return;
            }
            
            this.submit();
        });
    </script>
</body>
</html> 
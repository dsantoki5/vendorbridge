<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

$role     = $_SESSION['role'];
$name     = $_SESSION['name'];
$parts    = explode(' ', $name);
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

$approvals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM approvals WHERE status='Pending'"));
$rfqCount  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM rfqs"));

if (isset($_POST['generate'])) {
    $po_id = (int) $_POST['po_id'];

    $po = mysqli_query(
        $conn,
        "SELECT purchase_orders.*, vendors.vendor_name
         FROM purchase_orders
         LEFT JOIN vendors ON purchase_orders.vendor_id = vendors.id
         WHERE purchase_orders.id = '$po_id'
         LIMIT 1"
    );

    if ($row = mysqli_fetch_assoc($po)) {
        $subtotal       = $row['amount'];
        $tax            = round(($subtotal * 18) / 100, 2);
        $total          = round($subtotal + $tax, 2);
        $invoice_number = "INV-" . date("Ymd") . "-" . rand(100, 999);

        mysqli_query($conn,
            "INSERT INTO invoices
                (invoice_number, po_id, subtotal, tax, total, status, created_at)
             VALUES
                ('$invoice_number', '$po_id', '$subtotal', '$tax', '$total', 'pending', NOW())"
        );

        mysqli_query($conn,
            "INSERT INTO notifications (message, type)
             VALUES ('Invoice $invoice_number Generated', 'Invoice')"
        );

        mysqli_query($conn,
            "INSERT INTO activity_logs (activity, module_name)
             VALUES ('Invoice $invoice_number Generated Successfully', 'Invoice')"
        );

        header("Location: invoice_list.php?success=1");
        exit();
    } else {
        $error = "Selected Purchase Order not found.";
    }
}

// Fetch POs — exclude those that already have an invoice
$result = mysqli_query(
    $conn,
    "SELECT purchase_orders.*, vendors.vendor_name
     FROM purchase_orders
     LEFT JOIN vendors ON purchase_orders.vendor_id = vendors.id
     LEFT JOIN invoices ON purchase_orders.id = invoices.po_id
     WHERE invoices.id IS NULL
     ORDER BY purchase_orders.id DESC"
);

$pos = [];
while ($po = mysqli_fetch_assoc($result)) {
    $pos[] = $po;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Invoice — VendorBridge ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
<div class="dashboard-container">

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar">

        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg width="20" height="20" viewBox="0 0 28 28" fill="none">
                    <path d="M4 8h20v14a2 2 0 01-2 2H6a2 2 0 01-2-2V8z"
                          fill="rgba(255,255,255,0.85)"/>
                    <path d="M2 8h24M10 8V5a2 2 0 012-2h4a2 2 0 012 2v3"
                          stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h2>VendorBridge</h2>
                <span>ERP Platform</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="sidebar-section-label">Main Menu</div>
            <ul>
                <li>
                    <a href="../dashboard.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2
                                     2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0
                                     011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="../vendor/vendor_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10
                                     0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3
                                     3 0 015.356-1.857M7 20v-2c0-.656.126-1.283
                                     .356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3
                                     3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Vendors
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">Procurement</div>
            <ul>
                <li>
                    <a href="../rfq/rfq_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0
                                     012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1
                                     0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        RFQs
                        <span class="sidebar-badge"><?php echo $rfqCount; ?></span>
                    </a>
                </li>
                <li>
                    <a href="../quotation/quotation_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1
                                     1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2
                                     2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002
                                     2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Quotations
                    </a>
                </li>
                <li>
                    <a href="../quotation/compare_quotation.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                                     002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0
                                     012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2
                                     2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2
                                     0 01-2-2z"/>
                        </svg>
                        Compare Quotations
                    </a>
                </li>
                <li>
                    <a href="../approval/approval_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approvals
                        <?php if ($approvals > 0): ?>
                        <span class="sidebar-badge"><?php echo $approvals; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="../purchase_order/po_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Purchase Orders
                    </a>
                </li>
                <li>
                    <a href="invoice_list.php" class="active">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                                     002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2
                                     2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2
                                     0 014 0z"/>
                        </svg>
                        Invoices
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">Reports</div>
            <ul>
                <li>
                    <a href="../reports/analytics.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        Analytics
                    </a>
                </li>
                <li>
                    <a href="../logs/activity_log.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Activity Logs
                    </a>
                </li>
            </ul>

        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?php echo $initials; ?></div>
                <div class="sidebar-user-info">
                    <strong><?php echo htmlspecialchars($name); ?></strong>
                    <small><?php echo htmlspecialchars($role); ?></small>
                </div>
            </div>
            <ul style="margin-top:6px;">
                <li>
                    <a href="../logout.php" style="color:rgba(255,100,100,0.80);">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3
                                     3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

    </aside>
    <!-- END SIDEBAR -->

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">Generate Invoice</div>

            <div class="topbar-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search...">
            </div>

            <div class="topbar-icon-btn">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118
                             14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0
                             10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0
                             .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3
                             0 11-6 0v-1m6 0H9"/>
                </svg>
                <?php if ($approvals > 0): ?>
                <span class="topbar-notif-dot"></span>
                <?php endif; ?>
            </div>

            <span class="badge badge-info"><?php echo htmlspecialchars($role); ?></span>
        </div>

        <!-- PAGE BODY -->
        <div class="page-body">

            <!-- BREADCRUMB -->
            <div class="breadcrumb">
                <a href="../dashboard.php">Dashboard</a>
                <span class="breadcrumb-sep">›</span>
                <a href="invoice_list.php">Invoices</a>
                <span class="breadcrumb-sep">›</span>
                <span class="current">Generate Invoice</span>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom:20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if (empty($pos)): ?>

            <!-- Empty State -->
            <div class="form-container">
                <div class="empty-state" style="padding:80px 24px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h3>No Purchase Orders Available</h3>
                    <p>All existing POs already have invoices, or no POs have been created yet.</p>
                    <a href="../purchase_order/po_list.php" class="btn-action"
                       style="margin-top:16px; display:inline-flex;">
                        View Purchase Orders
                    </a>
                </div>
            </div>

            <?php else: ?>

            <script>
                const poData = <?php echo json_encode($pos); ?>;
            </script>

            <div class="form-container">

                <!-- Form Header -->
                <div class="form-header">
                    <div class="form-header-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                             stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                                     002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2
                                     2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2
                                     0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="form-title">Generate Invoice</div>
                        <div class="form-subtitle">
                            Select a Purchase Order to create an invoice with 18% GST applied
                        </div>
                    </div>
                </div>

                <form method="POST" id="invoiceForm">
                <div class="form-body">

                    <!-- PO Select -->
                    <div class="form-row">
                        <label class="required">Select Purchase Order</label>
                        <select name="po_id" id="poSelect" required>
                            <option value="">— Choose a Purchase Order —</option>
                            <?php foreach ($pos as $po): ?>
                            <option
                                value="<?php echo $po['id']; ?>"
                                data-amount="<?php echo $po['amount']; ?>"
                                data-vendor="<?php echo htmlspecialchars($po['vendor_name'] ?? '—'); ?>"
                                data-po="<?php echo htmlspecialchars($po['po_number']); ?>"
                            >
                                <?php echo htmlspecialchars($po['po_number']); ?>
                                <?php if (!empty($po['vendor_name'])): ?>
                                — <?php echo htmlspecialchars($po['vendor_name']); ?>
                                <?php endif; ?>
                                — ₹<?php echo number_format($po['amount'], 2); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Live Invoice Preview -->
                    <div id="previewCard" style="display:none; margin-top:8px;">
                        <div class="alert alert-info"
                             style="flex-direction:column; align-items:flex-start;
                                    gap:16px; padding:20px;">

                            <div style="display:flex; align-items:center; gap:8px;
                                        font-weight:700; font-size:13px;
                                        color:var(--info-text); text-transform:uppercase;
                                        letter-spacing:0.5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                Invoice Preview
                            </div>

                            <!-- Row 1: PO + Vendor + Invoice No -->
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr;
                                        gap:16px; width:100%;">
                                <div>
                                    <div style="font-size:11px; font-weight:700;
                                                text-transform:uppercase; letter-spacing:0.5px;
                                                color:var(--info-text); opacity:0.7;
                                                margin-bottom:4px;">PO Number</div>
                                    <div id="prevPO" style="font-size:14px; font-weight:600;
                                         color:var(--navy);">—</div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700;
                                                text-transform:uppercase; letter-spacing:0.5px;
                                                color:var(--info-text); opacity:0.7;
                                                margin-bottom:4px;">Vendor</div>
                                    <div id="prevVendor" style="font-size:14px; font-weight:600;
                                         color:var(--navy);">—</div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700;
                                                text-transform:uppercase; letter-spacing:0.5px;
                                                color:var(--info-text); opacity:0.7;
                                                margin-bottom:4px;">Invoice No.</div>
                                    <div id="prevInvNo" style="font-size:14px; font-weight:600;
                                         color:var(--navy);">Auto-generated</div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div style="width:100%; height:1px;
                                        background:rgba(14,165,233,0.15);"></div>

                            <!-- Row 2: Amounts -->
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr;
                                        gap:16px; width:100%;">
                                <div>
                                    <div style="font-size:11px; font-weight:700;
                                                text-transform:uppercase; letter-spacing:0.5px;
                                                color:var(--info-text); opacity:0.7;
                                                margin-bottom:4px;">Subtotal</div>
                                    <div id="prevSubtotal" style="font-size:15px; font-weight:600;
                                         color:var(--navy);">—</div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700;
                                                text-transform:uppercase; letter-spacing:0.5px;
                                                color:var(--info-text); opacity:0.7;
                                                margin-bottom:4px;">GST (18%)</div>
                                    <div id="prevTax" style="font-size:15px; font-weight:600;
                                         color:var(--warning);">—</div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700;
                                                text-transform:uppercase; letter-spacing:0.5px;
                                                color:var(--info-text); opacity:0.7;
                                                margin-bottom:4px;">Total Payable</div>
                                    <div id="prevTotal" style="font-size:18px; font-weight:700;
                                         color:var(--success);">—</div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /form-body -->

                <div class="form-actions">
                    <button type="submit" name="generate" class="btn-save"
                            id="submitBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Generate Invoice
                    </button>
                    <a href="invoice_list.php" class="btn-back">Cancel</a>
                </div>
                </form>

            </div><!-- /form-container -->
            <?php endif; ?>

        </div><!-- /page-body -->
    </div><!-- /main-content -->
</div><!-- /dashboard-container -->

<script>
(function () {
    const select      = document.getElementById('poSelect');
    const preview     = document.getElementById('previewCard');
    const prevPO      = document.getElementById('prevPO');
    const prevVendor  = document.getElementById('prevVendor');
    const prevInvNo   = document.getElementById('prevInvNo');
    const prevSubtotal= document.getElementById('prevSubtotal');
    const prevTax     = document.getElementById('prevTax');
    const prevTotal   = document.getElementById('prevTotal');
    const submitBtn   = document.getElementById('submitBtn');

    if (!select) return;

    const lookup = {};
    poData.forEach(p => { lookup[p.id] = p; });

    function fakeInvNumber() {
        const d   = new Date();
        const ymd = d.getFullYear()
            + String(d.getMonth() + 1).padStart(2, '0')
            + String(d.getDate()).padStart(2, '0');
        return 'INV-' + ymd + '-XXX';
    }

    function formatINR(val) {
        return '₹ ' + parseFloat(val).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    select.addEventListener('change', function () {
        const id = this.value;
        if (!id) {
            preview.style.display = 'none';
            submitBtn.disabled = true;
            return;
        }

        const p = lookup[id];
        if (p) {
            const subtotal = parseFloat(p.amount);
            const tax      = Math.round(subtotal * 18) / 100;
            const total    = subtotal + tax;

            prevPO.textContent       = p.po_number;
            prevVendor.textContent   = p.vendor_name || '—';
            prevInvNo.textContent    = fakeInvNumber();
            prevSubtotal.textContent = formatINR(subtotal);
            prevTax.textContent      = formatINR(tax);
            prevTotal.textContent    = formatINR(total);

            preview.style.display = 'block';
            submitBtn.disabled    = false;
        }
    });
})();
</script>

</body>
</html>
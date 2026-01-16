<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    $sql_dep = "SELECT * FROM deposits WHERE id='$id'";
    $res_dep = $conn->query($sql_dep);
    $deposit = $res_dep->fetch_assoc();

    if ($action == 'approve' && $deposit['status'] == 'Pending') {
        $amount = $deposit['amount'];
        $user_email = $deposit['user_email'];

        $sql_update_bal = "UPDATE users SET balance = balance + $amount WHERE email='$user_email'";
        $conn->query($sql_update_bal);

        $sql_update_status = "UPDATE deposits SET status='Approved' WHERE id='$id'";
        $conn->query($sql_update_status);
        
        echo "<script>alert('Deposit Approved & Balance Added!'); window.location.href='admin_deposits.php';</script>";

    } elseif ($action == 'reject') {
        $sql_reject = "UPDATE deposits SET status='Rejected' WHERE id='$id'";
        $conn->query($sql_reject);
        echo "<script>alert('Deposit Rejected!'); window.location.href='admin_deposits.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Deposits - Admin</title>
    <style>
        body { background-color: #0f172a; color: white; font-family: sans-serif; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h2 { text-align: center; color: #fbbf24; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #334155; text-align: center; }
        th { background: #334155; color: #fbbf24; }
        .btn { padding: 5px 10px; border-radius: 5px; text-decoration: none; color: white; font-size: 12px; }
        .btn-approve { background: #22c55e; }
        .btn-reject { background: #ef4444; }
        .back-btn { display: block; margin-top: 20px; text-align: center; color: #94a3b8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Deposit Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>User Email</th>
                    <th>Method</th>
                    <th>Number</th>
                    <th>TrxID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM deposits ORDER BY id DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td><?php echo $row['user_email']; ?></td>
                    <td><?php echo $row['method']; ?></td>
                    <td><?php echo $row['sender_number']; ?></td>
                    <td><?php echo $row['trx_id']; ?></td>
                    <td><b>৳<?php echo $row['amount']; ?></b></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <?php if($row['status'] == 'Pending') { ?>
                            <a href="admin_deposits.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-approve" onclick="return confirm('Approve?');">Approve</a>
                            <a href="admin_deposits.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-reject" onclick="return confirm('Reject?');">Reject</a>
                        <?php } else { echo "-"; } ?>
                    </td>
                </tr>
                <?php } } else { echo "<tr><td colspan='7'>No requests found</td></tr>"; } ?>
            </tbody>
        </table>
        <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>
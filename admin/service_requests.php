<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";
require_once "../config/admin_auth.php";

requireAdmin();

$error = "";
$success = "";


/* ================================
   UPDATE REQUEST STATUS
================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {

        $error = "Invalid security token.";

    } else {

        $request_id = intval($_POST["request_id"] ?? 0);
        $status = $_POST["status"] ?? "";

        $allowed_statuses = [
            "Pending",
            "Approved",
            "In Progress",
            "Completed",
            "Rejected"
        ];

        if (
            $request_id <= 0 ||
            !in_array($status, $allowed_statuses)
        ) {

            $error = "Invalid request or status.";

        } else {

            $stmt = $conn->prepare(
                "UPDATE service_requests
                 SET status = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "si",
                $status,
                $request_id
            );

            if ($stmt->execute()) {

                $success = "Service request status updated.";

            } else {

                $error = "Unable to update the request.";

            }

            $stmt->close();
        }
    }
}


/* ================================
   GET SERVICE REQUESTS
================================ */

$sql = "
    SELECT
        service_requests.id,
        service_requests.service,
        service_requests.description,
        service_requests.status,
        service_requests.created_at,
        users.username
    FROM service_requests
    INNER JOIN users
        ON service_requests.user_id = users.id
    ORDER BY service_requests.created_at DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Service Requests | JG Digital Solutions</title>

    <link rel="stylesheet" href="style.css">

</head>


<body class="admin-page">


<header class="admin-header">

    <div class="admin-header-content">

        <img src="../image/Asset 1.png" class="logo" alt="JG Digital Solutions Logo">


        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="messages.php">Messages</a>
            <a href="service_requests.php"> Service Requests</a>
            <a href="logout.php">Logout</a>
        </nav>

    </div>

</header>



<main class="admin-container">


    <div class="admin-heading">

        <h1>Service Requests</h1>

        <p>
            Manage service requests submitted by users.
        </p>

    </div>


    <?php if ($error !== ""): ?>

        <div class="form-error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($success !== ""): ?>

        <div class="form-success">
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>



    <div class="table-container">

        <table class="admin-table service-request-table">

            <thead>

                <tr>

                    <th>ID</th>

                    <th>User</th>

                    <th>Service</th>

                    <th>Description</th>

                    <th>Status</th>

                    <th>Date</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($request = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["id"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["username"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $request["service"]
                                );
                                ?>
                            </td>


                            <td class="request-description">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $request["description"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <?php

                                $statusClass = strtolower(
                                    str_replace(
                                        " ",
                                        "-",
                                        $request["status"]
                                    )
                                );

                                ?>

                                <span
                                    class="request-status status-<?php echo $statusClass; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $request["status"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $request["created_at"]
                                );
                                ?>

                            </td>


                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="request_id"
                                        value="<?php echo $request["id"]; ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
                                    >


                                    <select
                                        name="status"
                                        class="status-select"
                                    >

                                        <option
                                            value="Pending"
                                            <?php echo $request["status"] === "Pending" ? "selected" : ""; ?>
                                        >
                                            Pending
                                        </option>


                                        <option
                                            value="Approved"
                                            <?php echo $request["status"] === "Approved" ? "selected" : ""; ?>
                                        >
                                            Approved
                                        </option>


                                        <option
                                            value="In Progress"
                                            <?php echo $request["status"] === "In Progress" ? "selected" : ""; ?>
                                        >
                                            In Progress
                                        </option>


                                        <option
                                            value="Completed"
                                            <?php echo $request["status"] === "Completed" ? "selected" : ""; ?>
                                        >
                                            Completed
                                        </option>


                                        <option
                                            value="Rejected"
                                            <?php echo $request["status"] === "Rejected" ? "selected" : ""; ?>
                                        >
                                            Rejected
                                        </option>

                                    </select>


                                    <button
                                        type="submit"
                                        class="update-button"
                                    >
                                        UPDATE
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">
                            No service requests have been submitted yet.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <a
        href="dashboard.php"
        class="back-button"
    >
        ← Back to Dashboard
    </a>


</main>


</body>

</html>
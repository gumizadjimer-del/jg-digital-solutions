<?php

session_start();

require_once "../config/database.php";
require_once "../config/security.php";
require_once "../config/admin_auth.php";

requireLogin();

if ($_SESSION["role"] !== "user") {
    header("Location: ../admin/dashboard.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$error = "";
$success = "";


/* ================================
   SUBMIT SERVICE REQUEST
================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {

        $error = "Invalid security token.";

    } else {

        $service = trim($_POST["service"] ?? "");
        $description = trim($_POST["description"] ?? "");

        if ($service === "" || $description === "") {

            $error = "Please fill in all fields.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO service_requests
                (user_id, service, description)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "iss",
                $user_id,
                $service,
                $description
            );

            if ($stmt->execute()) {

                $success = "Your service request has been submitted.";

            } else {

                $error = "Unable to submit your request.";

            }

            $stmt->close();
        }
    }
}


/* ================================
   GET USER REQUESTS
================================ */

$stmt = $conn->prepare(
    "SELECT id, service, description, status, created_at
     FROM service_requests
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Service Requests | JG Digital Solutions</title>

    <link rel="stylesheet" href="../admin/style.css">

</head>


<body class="dashboard-page">


<header class="admin-header">

    <div class="admin-header-content">

        <img src="../image/Asset 1.png" class="logo" alt="JG Digital Solutions Logo">


        <nav class="admin-nav">

        <a href="../admin/logout.php">Logout</a>
        </nav>

    </div>

</header>


<main class="dashboard-container">


    <div class="dashboard-heading">

        <h1>Service Requests</h1>

        <p>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["username"]); ?>.
        </p>

    </div>


    <!-- SERVICE REQUEST FORM -->

    <div class="request-card">

        <h2>Request a Service</h2>

        <p>
            Choose a service and tell us what you need.
        </p>


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


        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars(getCsrfToken()); ?>"
            >


            <div class="form-group">

                <label for="service">
                    Service
                </label>

                <select
                    id="service"
                    name="service"
                    required
                >

                    <option value="">
                        Select a service
                    </option>

                    <option value="Web Development">
                        Web Development
                    </option>

                    <option value="Software Development">
                        Software Development
                    </option>

                    <option value="Graphic & Brand Design">
                        Graphic & Brand Design
                    </option>

                    <option value="Data & Simulation">
                        Data & Simulation
                    </option>

                    <option value="Custom Digital Solutions">
                        Custom Digital Solutions
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    placeholder="Describe what you need..."
                    required
                ></textarea>

            </div>


            <button
                type="submit"
                class="auth-button"
            >
                SUBMIT SERVICE REQUEST
            </button>

        </form>

    </div>



    <!-- USER REQUESTS -->

    <div class="requests-section">

        <h2>My Service Requests</h2>


        <div class="table-container">

            <table class="admin-table request-table">

                <thead>

                    <tr>

                        <th>Service</th>

                        <th>Description</th>

                        <th>Status</th>

                        <th>Date</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($request = $result->fetch_assoc()): ?>

                            <tr>

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

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="4">
                                You have not submitted any service requests yet.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


</main>


</body>

</html>
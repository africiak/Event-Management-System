<?php
include 'connection.php';

$message ='';

if ($_SERVER ["REQUEST_METHOD"] === "POST") { 

     if (isset($_POST['delete_id'])) {
        $delete_id = (int) $_POST['delete_id'];
        try{
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE resource_id = :id");
            $stmt->execute([':id' => $delete_id]);
            $bookingCount = $stmt->fetchColumn();

            if ($bookingCount > 0) {
                echo "<script>alert('This resource is currently booked.'); window.location.href='adminutil.php';</script>";
                exit();
            }

            $stmt = $pdo->prepare("UPDATE resources set status= 'inactive' WHERE id = :id");
            $stmt->execute([':id' => $delete_id]);

            echo "<script>alert('Resource archived successfully'); window.location.href='adminutil.php';</script>";
            exit();

        } catch (PDOException $e) {
            echo "<script>alert('Error archiving resource: " . $e->getMessage() . "'); window.location.href='adminutil.php';</script>";
            exit();
        }
    }

    $resource_name = trim($_POST['resource_name'] ?? '');
    $resource_type = trim($_POST['resource_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if (!preg_match('/^[a-zA-Z0-9\s\-_,.()]+$/', $resource_name)) {
        echo "<script>alert('Invalid characters in resource name.'); window.history.back();</script>";
        exit();
    }

    if (!preg_match('/^[a-zA-Z\s]+$/', $resource_type)) {
        echo "<script>alert('Invalid characters in resource type.'); window.history.back();</script>";
        exit();
    }

    if (strlen($description) > 500) {
        echo "<script>alert('Description is too long. Max 500 characters.'); window.history.back();</script>";
        exit();
    }

    if( empty($resource_name) || empty($resource_type) || empty($quantity)) {
        echo "Please fill in all required fields correctly";
        exit();
    }

    try {
        if($id){
            $stmt = $pdo->prepare(
                "UPDATE resources 
                SET resource_name = :resource_name,
                resource_type = :resource_type,
                quantity = :quantity,
                description = :description
                WHERE id = :id"
            );
            $stmt->execute([
                ':resource_name' => $resource_name,
                ':resource_type' => $resource_type,
                ':quantity' => $quantity,
                ':description' => $description,
                ':id' => $id

            ]);

                 echo "<script> alert('Resource updated successfully'); window.location.href='adminutil.php'; </script>";

        } else{

        $stmt = $pdo->prepare("INSERT INTO resources ( resource_name, resource_type, quantity, description)
         VALUES (:resource_name, :resource_type, :quantity, :description)");

         $stmt->execute([
            ':resource_name' => $resource_name,
            ':resource_type' => $resource_type,
            ':quantity' => $quantity,
            ':description' => $description
         ]);

         echo "<script> alert('Resource added successfully'); window.location.href='adminutil.php'; </script>";
        }
    } catch (PDOException $e) {
        echo "<script> alert('Error adding new resource: ') </script>" . $e->getMessage(); 
    }
}

try{
    $stmt = $pdo->prepare("SELECT * FROM resources");
    $stmt-> execute();
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
    die("Error:" . $e->getMessage());
}

$edit_mode = false;
$edit_data = [];

if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];

    $stmt = $pdo->prepare("SELECT * FROM resources WHERE id = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($edit_data) {
        $edit_mode = true;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/adminutil.css">
    <title>ADMIN</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
    <nav class="custom_nav">
        <div class="logo">
            <img src="../img/logowhite.png" alt="bee icon" width="30px" height="30px">
        </div>
        <div class="nav-links">
            <a href="admin.php" class="nav-item" data-text="Dashboard"><i class="fas fa-chart-line"></i>
                <p style="font-size:13px; padding-left:5px;">Home</p>
            </a>
            <a href="adminevents.php" class="nav-item" data-text="resources"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="adminusers.php" class="nav-item" data-text="Users"><i class="fas fa-users"></i>
                <p style="font-size:13px; padding-left:5px;">Users</p>
            </a>
            <a href="adminutil.php" class="nav-item" data-text="Resources"><i class="fas fa-cubes"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <a href="reports.php" class="nav-item" data-text="Reports"><i class="fas fa-file-alt"></i>
                <p style="font-size:13px; padding-left:5px;">Reports</p>
            </a>
            <a href="adminlogs.php" class="nav-item" data-text="Analytics"><i class="fas fa-chart-bar"></i>
                <p style="font-size:13px; padding-left:5px;">Logs</p>
            </a>
            <a href="adminbudget.php" class="nav-item" data-text="Budget"><i class="fas fa-wallet"></i>
                <p style="font-size:13px; padding-left:5px;">Budget</p>
            </a>
            <a href="adminprofile.php" class="nav-item" data-text="Profile"><i class="fas fa-user-cog"></i>
                <p style="font-size:13px; padding-left:5px;">Profile</p>
            </a>
            <a href="adminalert.php" class="nav-item" data-text="Notifications"><i class="fas fa-bell"></i>
                <p style="font-size:13px; padding-left:5px;">Alerts</p>
            </a>
        </div>
        <div class="exit">
            <a href="logout.php" onclick="confirmLogout(resource)" id="logout" style="padding-top:3px;">
                <i class=" fas
                fa-sign-out-alt"></i>
                <p>logout</p>
            </a>
        </div>
    </nav>
    <div class="container">
        <?php if (!empty($message)): ?>
        <div class="alert alert-info" style="margin-top: 10px;">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form id="addresourceForm" class="form-section" method="POST">
            <?php if ($edit_mode): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <?php endif; ?>

            <div class="title">
                <p>Resources Form
                    <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                </p>
            </div>
            <label for="resource_name">Resource</label>
            <input type="text" id="resource_name" name="resource_name" placeholder="Resource name"
                value="<?= $edit_mode ? htmlspecialchars($edit_data['resource_name']) : '' ?>" required>
            <label for="resource_type">Type</label>
            <select id="resource_type" name="resource_type" required>
                <option value="">Select Type</option>
                <option value="Equipment"
                    <?= $edit_mode && $edit_data['resource_type'] === 'Equipment' ? 'selected' : '' ?>>Equipment
                </option>
                <option value="Furniture"
                    <?= $edit_mode && $edit_data['resource_type'] === 'Furniture' ? 'selected' : '' ?>>Furniture
                </option>
                <option value="Service"
                    <?= $edit_mode && $edit_data['resource_type'] === 'Service' ? 'selected' : '' ?>>Service</option>
            </select>
            <label for="quantity">Available Quantity</label>
            <input type="number" id="quantity" name="quantity" min="1" placeholder="e.g. 5"
                #FF6D1Fvalue="<?= $edit_mode ? $edit_data['quantity'] : '' ?>">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"
                placeholder="Optional details (size, usage rules, etc.)"><?= $edit_mode ? htmlspecialchars($edit_data['description']) : '' ?></textarea>
            <button type="submit"><?= $edit_mode ? 'Update Resource' : 'Add Resource' ?></button>
        </form>
        <div class="resource" style="height:560px; overflow-y: auto; margin-left: 30px; width:700px;">
            <div class="logo" style="font-family:'poppins'; font-size:20px; color:#FF6D1F">
                Resources <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
            </div>
            <table class="table table-hover" style=" margin-left:60px;">
                <thead>
                    <tr>
                        <th scope="col">Resource</th>
                        <th scope="col">type</th>
                        <th scope="col">Available</th>
                        <th scope="col">Description</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resources as $res): ?>
                    <tr>

                        <td scope="row"><?= htmlspecialchars($res["resource_name"])?></td>
                        <td><?= htmlspecialchars($res["resource_type"])?></td>
                        <td><?= htmlspecialchars($res["quantity"])?></td>
                        <td><?= htmlspecialchars($res["description"])?></td>
                        <td><?= htmlspecialchars($res["status"])?></td>
                        <td style="display: flex; flex-direction: row; gap:5px;">
                            <form method="GET" action="adminutil.php">
                                <input type="hidden" name="edit_id" value="<?= $res['id'] ?>">
                                <button type="submit" class="btn btn-secondary">
                                    Edit
                                </button>
                            </form>
                            <form action="adminutil.php" method="POST"
                                onsubmit="return confirm('Are you sure you want to archive this resource?');">
                                <input type="hidden" name="delete_id" value="<?= $res['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="height: 38px;"><i
                                        class="fas fa-trash"></i></button>
                            </form>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="script.js"></script>

</body>

</html>
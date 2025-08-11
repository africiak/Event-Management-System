<?php
 
require 'connection.php';
require 'logger.php';
session_start();
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $_SESSION['rank'] !== 'President') {
    header("Location: unauthorized.php");
    exit();
}


try{
$stmt = $pdo->prepare("
    SELECT 
        e.event_id, 
        e.name, 
        e.budget AS allocated_budget,
        IFNULL(SUM(b.amount), 0) AS used_budget
    FROM events e
    LEFT JOIN budget_items b ON e.event_id = b.eventid
    WHERE e.status = 'Approved' AND e.activity_status = 'active'
    GROUP BY e.event_id
");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
    die("Error:" . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = $_POST['event_id'];
    $item = trim($_POST['item']);
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $created_by = $_SESSION['user_id'];
    $id = isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null;


try{
    if($id){
         $stmt = $pdo->prepare("
                UPDATE budget_items 
                SET eventid = :eventid, item = :item, amount = :amount, description = :description 
                WHERE id = :id
            ");
            $stmt->execute([
                ':eventid' => $event_id,
                ':item' => $item,
                ':amount' => $amount,
                ':description' => $description,
                ':id' => $id
            ]);
            logActivity($pdo, $created_by, 'Update Budget Item', "Updated item '{$item}' (ID: {$id})");
                        
            echo "<script>alert('Budget item updated successfully'); window.location.href='budget.php';</script>";
    } else{
            $stmt = $pdo->prepare("
                INSERT INTO budget_items (eventid, item, amount, description, created_by, created_at)
                VALUES (:eventid, :item, :amount, :description, :created_by, NOW())
            ");

            $stmt->execute([
                ':eventid' => $_POST['event_id'],
                ':item' => trim($_POST['item']),
                ':amount' => floatval($_POST['amount']),
                ':description' => trim($_POST['description']),
                ':created_by' => $_SESSION['user_id']
            ]);
            logActivity($pdo, $created_by, 'Add Budget Item', "Added new item '{$item}' for event ID {$event_id}");

            echo "<script> alert('budget item added successfully '); window.location.href='budget.php'; </script>";
        }
}catch(PDOException $e){
    die("Error:" . $e->getMessage());
}
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.id, 
            b.eventid,
            b.item,
            b.amount,
            b.description,
            e.name AS event_name
        FROM budget_items b
        JOIN events e ON b.eventid = e.event_id
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budgeting</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/budget.css">
</head>

<body>
    <?php
if (isset($_SESSION['deleted'])):
?>
    <div class="alert alert-success">Budget item deleted successfully.</div>
    <?php
unset($_SESSION['deleted']); 
endif;
?>

    <div class="container">
        <div class="form">
            <div class="logo" style="font-family:'poppins'; font-size:25px; color:#FF6D1F ; margin-bottom: 10px;">
                <strong>Budget</strong> <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
            </div>
            <form action="" method="POST" id="budgetForm">
                <input type="hidden" name="id" value="<?= isset($_GET['id']) ? (int)$_GET['id'] : '' ?>">
                <label for="eventid"><strong>Select Event:</strong></label>
                <select name="event_id" id="eventSelect" required onchange="showBudget()">
                    <option value="">-- Select Event --</option>
                    <?php foreach ($events as $event): ?>
                    <option value="<?= $event['event_id'] ?>" data-budget="<?= $event['allocated_budget'] ?>"
                        data-used="<?= $event['used_budget'] ?>"
                        <?= isset($_GET['event_id']) && $_GET['event_id'] == $event['event_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($event['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <!-- Budget Display -->
                <div style="margin: 10px 0;">
                    <small id="budgetDisplay" style="color: grey; font-style: italic;">Select an event to see
                        budget</small>

                </div>

                <label for="item"><strong>Item Name:</strong></label>
                <input type="text" name="item" required placeholder="e.g., Transport"
                    value="<?= isset($_GET['item']) ? htmlspecialchars($_GET['item'], ENT_QUOTES) : '' ?>"> <br>

                <label for="amount"><strong>Amount (KES):</strong></label>
                <input type="number" name="amount" min="0" step="0.01" required placeholder="e.g., 2000"
                    value="<?= isset($_GET['amount']) ? htmlspecialchars($_GET['amount'], ENT_QUOTES) : '' ?>"> <br>



                <label for="description"><strong>Description:</strong></label>
                <textarea name="description" rows="2" placeholder="Details (optional)">
                    <?= isset($_GET['description']) ? htmlspecialchars($_GET['description'], ENT_QUOTES) : '' ?></textarea>

                <button type="submit">
                    <?= isset($_GET['id']) ? 'Update Item' : 'Add Item' ?>
                </button>
            </form>


        </div>
        <div class="list">
            <div class="logo" style="font-family:'poppins'; font-size:20px; color:#FF6D1F ; margin-bottom: 10px;">
                <strong>Budget list</strong> <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
            </div>
            <table class="table table-hover" style=" margin-left:60px;">
                <thead>
                    <tr>
                        <th scole="col">Event</th>
                        <th scope="col">Item</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Description</th>
                        <th scope="col">Action</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <form method="GET" action="budget.php">
                        <tr>
                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                            <input type="hidden" name="event_id" value="<?= (int)$item['eventid'] ?>">
                            <input type="hidden" name="item" value="<?= htmlspecialchars($item['item'], ENT_QUOTES) ?>">
                            <input type="hidden" name="amount"
                                value="<?= htmlspecialchars($item['amount'], ENT_QUOTES) ?>">
                            <input type="hidden" name="description"
                                value="<?= htmlspecialchars($item['description'], ENT_QUOTES) ?>">


                            <td><?= htmlspecialchars($item['event_name']) ?></td>
                            <td><?= htmlspecialchars($item["item"]) ?></td>
                            <td><?= htmlspecialchars(number_format($item["amount"], 2)) ?></td>
                            <td><?= htmlspecialchars($item["description"]) ?></td>
                            <td style="display: flex; flex-direction: row; gap: 5px;">
                                <button type="submit" class="btn btn-secondary">Edit</button>

                    </form>
                    <form action="delete_budget.php" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this item?');">
                        <input type="hidden" name="delete_item_id" value="<?= (int)$item['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="height: 38px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>
    <button style="margin-left: 100px; width:100px"><a href="dashboard.php"
            style="color:#fff; text-decoration: none;">back</a></button>
    <script>
    function showBudget() {
        const select = document.getElementById("eventSelect");
        const selectedOption = select.options[select.selectedIndex];

        const budget = parseFloat(selectedOption.getAttribute("data-budget")) || 0;
        const used = parseFloat(selectedOption.getAttribute("data-used")) || 0;
        const remaining = budget - used;

        const budgetDisplay = document.getElementById("budgetDisplay");
        if (budget > 0) {
            budgetDisplay.textContent =
                `allocated budget: KES ${parseFloat(budget).toFixed(2)}| Used: KES ${used.toFixed(2)} | Remaining: KES ${remaining.toFixed(2)}`;
        } else {
            budgetDisplay.textContent = "Select an event to see budget";
        }
    }
    </script>
    <script>
    // This removes the query string from the URL after the page loads
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
    }
    </script>

</body>

</html>
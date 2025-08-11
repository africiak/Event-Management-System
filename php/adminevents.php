<?php
session_start();
include 'connection.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/adminevents.css">
    <title>admin dashboard</title>
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
            <a href="adminevents.php" class="nav-item" data-text="Events"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="adminusers.php" class="nav-item" data-text="Users"><i class="fas fa-users"></i>
                <p style="font-size:13px; padding-left:5px;">Users</p>
            </a>
            <a href="adminutil.php" class="nav-item" data-text="Resources"><i class="fas fa-cubes"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <a href="calender.php" class="nav-item" data-text="Reports"><i class="fas fa-file-alt"></i>
                <p style="font-size:13px; padding-left:5px;">Reports</p>
            </a>
            <a href="notifications.php" class="nav-item" data-text="Analytics"><i class="fas fa-chart-bar"></i>
                <p style="font-size:13px; padding-left:5px;">Logs</p>
            </a>
            <a href="notifications.php" class="nav-item" data-text="Budget"><i class="fas fa-wallet"></i>
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
            <a href="logout.php" onclick="confirmLogout(event)" id="logout"> <i class="fas fa-sign-out-alt"></i>
                <p>logout</p>
            </a>
        </div>
    </nav>


    <div class="main_content">
        <div id="event-details">
            <p style="text-align: center; font-weight: bold;">
                Select an event to view details. <br>
                <img src="../img/hello.svg" alt="" width="500px" height="500px">
            </p>
        </div>
    </div>
    <div class="side_nav">
        <div class="logo">
            Events
        </div>
        <ul>
            <?php
        require 'connection.php';
        $stmt = $pdo->query("SELECT event_id, name, status FROM events ORDER BY created_at DESC");
        while ($event = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
            <li>
                <a href="#" class="event-link" data-event-id="<?= $event['event_id'] ?>">
                    <?= htmlspecialchars($event['name']) ?>
                </a>
                <?php
                            if($event['status'] === "Approved"){
                                echo "<span class='badge badge-success'>Approved</span>";
                            }elseif($event['status'] === "Pending"){
                                echo "<span class='badge badge-warning'>Pending</span>";
                            }elseif($event['status'] === "Rejected"){
                                echo "<span class='badge badge-danger'>Rejected</span>";
                            }
                            ?>

            </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $(".event-link").click(function(e) {
            e.preventDefault();

            let eventId = $(this).data("event-id");

            $.ajax({
                url: "fetch.php",
                type: "GET",
                data: {
                    event_id: eventId
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#event-details").html(`
                        <div class="event-container">
                        <div id="retrieved">
                        <h2>${response.data.event_name}</h2>
                        <p><strong>Event Date:</strong> ${response.data.date}</p>
                        <p><strong>Description:</strong> ${response.data.description}</p>
                        <p><strong>Category:</strong> ${response.data.category}</p>
                        <p><strong>Location:</strong> ${response.data.location}</p>
                        <p><strong>Organizer:</strong> ${response.data.organiser}</p>
                        <p><strong>Created By:</strong> ${response.data.created_by}</p>
                        <p><strong>Proposed Budget:</strong> ${response.data.budget}</p>
                        <p><strong>Status:</strong> ${response.data.status}</p>
                        </div>
                        <!-- Approval Form -->
                        <form id="approval-form" style="width: 100%;padding-right: 100px;">
                        <input type="hidden" name="event_id" id="event_id" value="${response.data.event_id}">
                            <label for="comments"><strong>Approval Comments:</strong></label>
                            <textarea id="comments" name="comments" rows="3" placeholder="Add comments..." required></textarea><br>
                            
                            <button type="button" onclick="submitApproval(1)" style="color: white; background-color:green;border:none;border-radius:5px;">Approve</button>
                            <button type="button" onclick="submitApproval(0)" style="color:white; background-color: red;border:none;border-radius:5px;"> Reject</button>
                         </form>
                        </div>
                          <p id="approval-msg"></p>
                          </div>
                    `);
                    } else {
                        $("#event-details").html(
                            "<p style='text-align: center; font-weight: bold;'>Event not found.</p>"
                        );
                    }
                },
                error: function() {
                    $("#event-details").html(
                        "<p style='text-align: center; font-weight: bold;'>Error fetching event details.</p>"
                    );
                }
            });
        });
    });

    //submit decision
    function submitApproval(decision) {
        let eventId = $("#event_id").val();
        let comments = $("#comments").val();

        $.ajax({
            url: "process_approval.php",
            type: "POST",
            data: {
                event_id: eventId,
                decision: decision,
                comments: comments
            },
            dataType: "json",
            success: function(response) {
                console.log("Server Response:", response);

                if (response.success) {
                    $("#approval-msg").html(
                        "<strong style='color: green;'>" + response.message + "</strong>"
                    );
                    setTimeout(() => location.reload(), 1000);
                } else {
                    $("#approval-msg").html(
                        "<strong style='color: red;'>" + response.message + "</strong>"
                    );
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error:", status, error);
                $("#approval-msg").html("<strong style='color: red;'>Error submitting approval.</strong>");
            }
        });
    }
    </script>
    <script src="script.js"></script>


</body>

</html>
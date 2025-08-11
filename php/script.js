function confirmLogout(event) {
  event.preventDefault(); // Stops the link from navigating right away
  if (confirm("Are you sure you want to log out?")) {
    window.location.href = "logout.php";
  }
}

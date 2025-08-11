document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById("eventStatusChart").getContext("2d");

  const eventStatusChart = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Approved", "Pending", "Rejected"],
      datasets: [
        {
          label: "Event Status",
          data: [
            parseInt(document.getElementById("approvedCount").value),
            parseInt(document.getElementById("pendingCount").value),
            parseInt(document.getElementById("rejectedCount").value),
          ],
          backgroundColor: [
            "rgba(34, 197, 94, 0.7)",
            "rgba(234, 179, 8, 0.7)",
            "rgba(239, 68, 68, 0.7)",
          ],
          borderColor: [
            "rgba(34, 197, 94, 1)",
            "rgba(234, 179, 8, 1)",
            "rgba(239, 68, 68, 1)",
          ],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom",
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              const value = context.parsed;
              const label = context.label;
              return `${label}: ${value}`;
            },
          },
        },
      },
    },
  });
});

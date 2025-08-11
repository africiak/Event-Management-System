document.addEventListener("DOMContentLoaded", function () {
  console.log("Labels:", resourceLabels);
  console.log("Values:", resourceValues);
  const ctx = document.getElementById("resourceBarChart");
  if (ctx && typeof resourceLabels !== "undefined") {
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: resourceLabels,
        datasets: [
          {
            label: "Bookings",
            data: resourceValues,
            backgroundColor: "rgba(255, 109, 31, 0.7)",
            borderColor: "rgba(255, 109, 31, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
            },
          },
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                return "Bookings: " + context.raw;
              },
            },
          },
        },
      },
    });
  } else {
    console.error("Chart canvas not found or data missing");
  }
});

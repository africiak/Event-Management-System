document.addEventListener("DOMContentLoaded", () => {
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  const toggleConfirm = document.getElementById("toggleConfirm");
  const confirmInput = document.getElementById("confirmPassword");

  togglePassword.addEventListener("click", () => {
    const isPassword = passwordInput.getAttribute("type") === "password";
    passwordInput.setAttribute("type", isPassword ? "text" : "password");
    togglePassword.classList.toggle("fa-eye-slash");
    togglePassword.classList.toggle("fa-eye");
  });

  toggleConfirm.addEventListener("click", () => {
    const isPassword = confirmInput.getAttribute("type") === "password";
    confirmInput.setAttribute("type", isPassword ? "text" : "password");
    toggleConfirm.classList.toggle("fa-eye-slash");
    toggleConfirm.classList.toggle("fa-eye");
  });
});

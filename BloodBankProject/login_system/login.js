document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');

    loginForm.addEventListener('submit', function(event) {
        const role = document.getElementById('userRole').value;
        const userID = document.getElementById('userID').value.trim();
        const password = document.getElementById('password').value;

        // Basic Frontend Validation ONLY. If it fails, we stop the form.
        if (role === "" || userID === "" || password === "") {
            event.preventDefault(); // Stop form from going to PHP
            showError("Please fill out all required fields.");
        }
        
        // If everything is filled out, the JS does nothing else, 
        // and allows the HTML form to naturally POST to login_process.php
    });

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
});
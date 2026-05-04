document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.getElementById('registerForm');

    if(registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault(); 
            
            const name = document.getElementById('donorName').value;
            const bloodType = document.getElementById('bloodType').value;
            
            if(bloodType === "") {
                alert("Please select your blood type.");
                return;
            }

            // Simulate successful registration and redirect to login
            alert(`Registration successful for ${name}! Redirecting to login...`);
            window.location.href = "../login_system/login.html";
        });
    }
});
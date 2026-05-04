document.addEventListener("DOMContentLoaded", function() {
    
    // --- Navigation Logic ---
    const menuItems = document.querySelectorAll('.sidebar li');
    const sections = document.querySelectorAll('.view-section');

    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            menuItems.forEach(li => li.classList.remove('active'));
            this.classList.add('active');

            sections.forEach(sec => sec.classList.add('hidden'));
            
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });

    // --- Create User Logic (Use Case 4) ---
    const createUserForm = document.getElementById('createUserForm');
    const userTableBody = document.getElementById('userTableBody');

    if(createUserForm) {
        createUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('newUserName').value;
            const role = document.getElementById('newUserRole').value;
            const email = document.getElementById('newUserEmail').value;
            
            // Exception Handling Simulation (Duplicate ID)
            if(email === "staff_mk" || email === "mgr_zain") {
                alert("Error: A user with this ID/Email already exists. Please choose another.");
                return;
            }

            alert(`Success! Administrative Action Logged.\nAccount created for ${name} as ${role}.`);
            
            // Add new user to table
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${email}</td>
                <td>${name}</td>
                <td>${role}</td>
                <td><span class="badge safe">Active</span></td>
                <td><button class="action-btn danger" onclick="deactivateUser(this)">Deactivate</button></td>
            `;
            userTableBody.appendChild(newRow);
            createUserForm.reset();
        });
    }

    // --- Data Backup Logic ---
    const initiateBackupBtn = document.getElementById('initiateBackupBtn');
    const backupStatus = document.getElementById('backupStatus');

    if (initiateBackupBtn) {
        initiateBackupBtn.addEventListener('click', function() {
            initiateBackupBtn.disabled = true;
            backupStatus.style.display = "block";
            
            let progress = 0;
            const interval = setInterval(() => {
                progress += 20;
                backupStatus.textContent = `Encrypting and backing up database... ${progress}%`;
                
                if(progress >= 100) {
                    clearInterval(interval);
                    backupStatus.style.color = "#28a745";
                    backupStatus.textContent = "Backup Completed Successfully. Log Saved.";
                    document.getElementById('lastBackupTime').textContent = "Just now";
                    
                    setTimeout(() => {
                        initiateBackupBtn.disabled = false;
                        backupStatus.style.display = "none";
                        backupStatus.style.color = "#007bff";
                    }, 3000);
                }
            }, 500);
        });
    }
});

// Remove user function
function deactivateUser(btn) {
    if(confirm("Are you sure you want to revoke system access for this user?")) {
        const row = btn.closest('tr');
        row.querySelector('.badge').className = "badge critical";
        row.querySelector('.badge').textContent = "Deactivated";
        btn.disabled = true;
        btn.style.opacity = "0.5";
        alert("Account locked. Administrative action logged.");
    }
}
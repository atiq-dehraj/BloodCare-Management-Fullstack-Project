document.addEventListener("DOMContentLoaded", function() {
    
    // --- Navigation Logic ---
    const menuItems = document.querySelectorAll('.sidebar li');
    const sections = document.querySelectorAll('.view-section');

    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all tabs
            menuItems.forEach(li => li.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');

            // Hide all sections
            sections.forEach(sec => sec.classList.add('hidden'));
            
            // Show the target section
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });
});

// --- External Action Functions ---
function updateTemp(unitId) {
    let newTemp = prompt(`Enter new temperature reading for ${unitId} (in °C):`);
    if(newTemp) {
        if(newTemp > 6) {
            alert(`WARNING: ${newTemp}°C is too high! Generating Temperature Alert to Manager.`);
        } else {
            alert(`Status updated. ${unitId} logged at ${newTemp}°C.`);
        }
    }
}

function fulfillRequest(reqId, bloodType) {
    if(confirm(`Searching inventory for earliest expiring ${bloodType} bag... \nAssign to ${reqId}?`)) {
        alert(`Request ${reqId} fulfilled. Bag status updated to 'Dispatched'.`);
    }
}
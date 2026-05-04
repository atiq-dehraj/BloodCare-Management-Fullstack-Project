document.addEventListener("DOMContentLoaded", function() {
    
    // --- Navigation Logic ---
    const menuItems = document.querySelectorAll('.sidebar li');
    const sections = document.querySelectorAll('.view-section');

    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all menu items
            menuItems.forEach(li => li.classList.remove('active'));
            // Add active class to the clicked menu item
            this.classList.add('active');

            // Hide all dashboard sections
            sections.forEach(sec => sec.classList.add('hidden'));
            
            // Find which section this tab is supposed to open
            const targetId = this.getAttribute('data-target');
            
            // Show the correct section
            if (document.getElementById(targetId)) {
                document.getElementById(targetId).classList.remove('hidden');
            }
        });
    });

});
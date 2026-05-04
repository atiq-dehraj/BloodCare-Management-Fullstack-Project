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

    // --- Generate Report Logic ---
    const generateReportBtn = document.getElementById('generateReportBtn');
    const reportPreview = document.getElementById('reportPreview');

    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', function() {
            const reportType = document.getElementById('reportType').options[document.getElementById('reportType').selectedIndex].text;
            
            // Show a quick loading simulation
            generateReportBtn.textContent = "Generating...";
            generateReportBtn.disabled = true;

            setTimeout(() => {
                reportPreview.classList.remove('hidden');
                generateReportBtn.textContent = "Generate PDF Report";
                generateReportBtn.disabled = false;
                alert(`${reportType} successfully generated from the database.`);
            }, 800);
        });
    }
});
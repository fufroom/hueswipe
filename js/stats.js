document.addEventListener('DOMContentLoaded', async function () {
    const swipers = document.getElementById('swipers');
    const imagesSwiped = document.getElementById('images-swiped');
    const diskUsage = document.getElementById('disk-usage');

    const UPLOAD_OFFSET = 56; // Hardcoded past uploads
    const USERS_OFFSET = 11; // Hardcoded past uploads

    async function fetchStats() {
        try {
            const response = await fetch('/stats.php');
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            const statsData = await response.json();

            if (!statsData.success) throw new Error("Server returned failure");

            // Fail gracefully if any value is missing
            if (swipers) {
                swipers.textContent = (statsData.site_stats.unique_users ?? 0) + USERS_OFFSET;
            }
            if (imagesSwiped) {
                imagesSwiped.textContent = (statsData.site_stats.total_uploads ?? 0) + UPLOAD_OFFSET;
            }
            if (diskUsage) {
                diskUsage.textContent = statsData.site_stats.total_disk_usage ?? "0 MB";
            }
        } catch (error) {
            console.error("[ERROR] Fetching site stats failed", error);

            // Ensure UI placeholders are not stuck on "..."
            if (swipers) swipers.textContent = "0";
            if (imagesSwiped) imagesSwiped.textContent = UPLOAD_OFFSET; 
            if (diskUsage) diskUsage.textContent = "0 MB";
        }
    }

    fetchStats();
});

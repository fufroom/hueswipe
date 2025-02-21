document.addEventListener('DOMContentLoaded', async function () {
    const swipers = document.getElementById('swipers');
    const imagesSwiped = document.getElementById('images-swiped');

    async function fetchStats() {
        try {
            const response = await fetch('/stats.php');
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
            const statsData = await response.json();

            if (statsData.success) {
                swipers.textContent = statsData.site_stats.unique_users;
                imagesSwiped.textContent = statsData.site_stats.total_uploads;
            } else {
                console.error("[ERROR] Failed to fetch site stats", statsData.error);
            }
        } catch (error) {
            console.error("[ERROR] Fetching site stats failed", error);
        }
    }

    fetchStats();
});

document.getElementById('sidebarToggle').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('sidebar-collapsed');

    // Mise à jour du contenu et du footer
    const mainContent = document.querySelector('.dashboard-content');
    const footer = document.querySelector('.dashboard-footer');

    if (sidebar.classList.contains('sidebar-collapsed')) {
        mainContent.style.marginLeft = "70px";
        footer.style.width = "calc(100% - 70px)";
        footer.style.marginLeft = "70px";
    } else {
        mainContent.style.marginLeft = "250px";
        footer.style.width = "calc(100% - 250px)";
        footer.style.marginLeft = "250px";
    }
});
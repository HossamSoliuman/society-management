document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', function(e) {
        if (sidebar && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });

    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function(dropdown) {
        const toggle = dropdown.querySelector('.dropdown-toggle, .icon-btn, .profile-btn');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdowns.forEach(function(d) {
                    if (d !== dropdown) d.classList.remove('open');
                });
                dropdown.classList.toggle('open');
            });
        }
    });

    document.addEventListener('click', function() {
        dropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('open');
        });
    });

    const tabContainers = document.querySelectorAll('[data-tabs]');
    tabContainers.forEach(function(container) {
        const tabs = container.querySelectorAll('[data-tab]');
        const panels = container.querySelectorAll('[data-tab-panel]');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                const target = this.getAttribute('data-tab');

                tabs.forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');

                panels.forEach(function(p) {
                    p.style.display = p.getAttribute('data-tab-panel') === target ? 'block' : 'none';
                });
            });
        });
    });

    const deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.3s ease';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        });
    }, 5000);
});

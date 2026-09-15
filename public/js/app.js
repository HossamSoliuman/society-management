document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var mobileQuery = window.matchMedia('(max-width: 768px)');

    function setSidebar(open) {
        if (!sidebar) { return; }
        sidebar.classList.toggle('open', open);
        document.body.classList.toggle('sidebar-open', open);
        if (sidebarToggle) { sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); }
    }

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            setSidebar(!sidebar.classList.contains('open'));
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() { setSidebar(false); });
    }

    document.addEventListener('click', function(e) {
        if (sidebar && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && sidebarToggle && !sidebarToggle.contains(e.target)) {
                setSidebar(false);
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
            setSidebar(false);
        }
    });

    // On phones, following a sidebar link closes the drawer
    if (sidebar) {
        sidebar.addEventListener('click', function(e) {
            var link = e.target.closest('a.nav-item');
            if (link && !link.classList.contains('nav-group-toggle') && mobileQuery.matches) {
                setSidebar(false);
            }
        });
    }

    // Leaving the mobile breakpoint resets the drawer state
    var onBreakpointChange = function(e) {
        if (!e.matches) { setSidebar(false); }
    };
    if (mobileQuery.addEventListener) {
        mobileQuery.addEventListener('change', onBreakpointChange);
    } else if (mobileQuery.addListener) {
        mobileQuery.addListener(onBreakpointChange);
    }

    // Sidebar accordion
    var navGroups = document.querySelectorAll('.nav-group');
    navGroups.forEach(function(group) {
        var toggle = group.querySelector('.nav-group-toggle');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                group.classList.toggle('open');
            });
        }
        // Auto-open if a child is active
        if (group.querySelector('.nav-item.active')) {
            group.classList.add('open');
        }
    });

    // Dropdowns — handle .dropdown, .notification-dropdown, .profile-dropdown
    var dropdownSelectors = ['.dropdown', '.notification-dropdown', '.profile-dropdown'];
    var allDropdowns = [];
    dropdownSelectors.forEach(function(sel) {
        document.querySelectorAll(sel).forEach(function(el) {
            if (allDropdowns.indexOf(el) === -1) {
                allDropdowns.push(el);
            }
        });
    });

    allDropdowns.forEach(function(dropdown) {
        var toggle = dropdown.querySelector('.dropdown-toggle, .icon-btn, .profile-btn');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                allDropdowns.forEach(function(d) {
                    if (d !== dropdown) { d.classList.remove('open'); }
                });
                dropdown.classList.toggle('open');
            });
        }
    });

    document.addEventListener('click', function() {
        allDropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('open');
        });
    });

    // Tabs
    var tabContainers = document.querySelectorAll('[data-tabs]');
    tabContainers.forEach(function(container) {
        var tabs = container.querySelectorAll('[data-tab]');
        var panels = container.querySelectorAll('[data-tab-panel]');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var target = this.getAttribute('data-tab');

                tabs.forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');

                panels.forEach(function(p) {
                    p.style.display = p.getAttribute('data-tab-panel') === target ? 'block' : 'none';
                });
            });
        });
    });

    // Delete confirm
    var deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Alert auto-hide
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.3s ease';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        });
    }, 5000);

    // Wizard
    var wizardForms = document.querySelectorAll('[data-wizard]');
    wizardForms.forEach(function(form) {
        var steps = form.querySelectorAll('.wizard-step');
        var indicators = form.querySelectorAll('.step-item');
        var prevBtn = form.querySelector('.wizard-prev');
        var nextBtn = form.querySelector('.wizard-next');
        var submitBtn = form.querySelector('.wizard-submit');
        var currentStep = 0;
        var totalSteps = steps.length;

        function showStep(index) {
            steps.forEach(function(s, i) {
                s.classList.toggle('active', i === index);
            });
            indicators.forEach(function(ind, i) {
                ind.classList.remove('active', 'completed');
                if (i === index) { ind.classList.add('active'); }
                if (i < index) { ind.classList.add('completed'); }
            });
            if (prevBtn) { prevBtn.style.display = index === 0 ? 'none' : 'inline-flex'; }
            if (nextBtn) { nextBtn.style.display = index === totalSteps - 1 ? 'none' : 'inline-flex'; }
            if (submitBtn) { submitBtn.style.display = index === totalSteps - 1 ? 'inline-flex' : 'none'; }

            if (index === totalSteps - 1) {
                buildReview(form);
            }
        }

        function validateStep(index) {
            var step = steps[index];
            var required = step.querySelectorAll('[required]');
            var valid = true;
            required.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            return valid;
        }

        function buildReview(form) {
            var reviewContent = form.querySelector('.review-content');
            if (!reviewContent) { return; }
            var rows = '';
            var seen = {};
            for (var i = 0; i < totalSteps - 1; i++) {
                var stepEl = steps[i];
                stepEl.querySelectorAll('input, select, textarea').forEach(function(field) {
                    if (!field.name || seen[field.name]) { return; }
                    if (field.type === 'radio' && !field.checked) { return; }
                    if (field.type === 'checkbox' && !field.checked) { return; }
                    seen[field.name] = true;
                    var label = field.name.replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                    var val = field.value || '—';
                    rows += '<tr><td style="font-weight:500;padding:8px 12px;color:var(--text-secondary);white-space:nowrap;">' + label + '</td><td style="padding:8px 12px;">' + val + '</td></tr>';
                });
            }
            reviewContent.innerHTML = rows ? '<table style="width:100%;border-collapse:collapse;">' + rows + '</table>' : '<p style="color:var(--text-muted);">No data entered yet.</p>';
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (validateStep(currentStep)) {
                    currentStep++;
                    showStep(currentStep);
                }
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (currentStep > 0) {
                    currentStep--;
                    showStep(currentStep);
                }
            });
        }

        showStep(0);
    });

    // Dashboard period filter — fetch fresh figures/charts and swap fragments
    var dashboardRange = document.getElementById('dashboardRange');
    if (dashboardRange) {
        var loader = document.getElementById('dashboardLoader');
        var content = document.querySelector('.page-content');

        dashboardRange.addEventListener('change', function () {
            var url = dashboardRange.getAttribute('data-url');
            var range = dashboardRange.value;

            if (loader) { loader.style.display = 'inline-block'; }
            if (content) { content.style.opacity = '0.6'; content.style.transition = 'opacity 0.15s ease'; }
            dashboardRange.disabled = true;

            fetch(url + '?range=' + encodeURIComponent(range), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (res) {
                    if (!res.ok) { throw new Error('Request failed: ' + res.status); }
                    return res.json();
                })
                .then(function (payload) {
                    var fragments = payload.fragments || {};
                    Object.keys(fragments).forEach(function (key) {
                        var target = document.getElementById('dash-' + key);
                        if (target) { target.innerHTML = fragments[key]; }
                    });
                })
                .catch(function (err) {
                    console.error('Dashboard update failed:', err);
                    alert('Could not update the dashboard. Please try again.');
                })
                .finally(function () {
                    if (loader) { loader.style.display = 'none'; }
                    if (content) { content.style.opacity = '1'; }
                    dashboardRange.disabled = false;
                });
        });
    }
});

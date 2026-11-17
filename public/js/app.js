/**
 * SmartHealthy Application JavaScript
 * Wilayah Puskesmas Astanajapura, Kabupaten Cirebon
 */

document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // Enhance radio card select styling
    const cardSelects = document.querySelectorAll('.card-select');
    cardSelects.forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio) {
            card.addEventListener('click', () => {
                radio.checked = true;
                // Update styling
                document.querySelectorAll('.card-select').forEach(c => {
                    c.classList.remove('border-primary', 'bg-primary-subtle');
                });
                card.classList.add('border-primary', 'bg-primary-subtle');
            });
        }
    });

    // Real-time Indonesian date clock if present
    const liveClockEl = document.getElementById('liveClock');
    if (liveClockEl) {
        const updateClock = () => {
            const now = new Date();
            liveClockEl.textContent = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }) + ' WIB';
        };
        updateClock();
        setInterval(updateClock, 1000);
    }

    // ==============================================================
    // SmartHealthy Notification System (Navbar Bell & Polling)
    // ==============================================================
    const notifRoot = document.getElementById('navbarNotifDropdownRoot');
    if (notifRoot) {
        const baseUrl = notifRoot.getAttribute('data-base-url') || '';
        const csrfToken = notifRoot.getAttribute('data-csrf') || '';
        const badgeEl = document.getElementById('navbarNotifBadge');
        const listEl = document.getElementById('navbarNotifList');
        const btnMarkAllRead = document.getElementById('btnMarkAllReadNavbar');
        let pollTimer = null;
        const POLL_INTERVAL = 30000; // 30 seconds

        const escapeHtml = (str) => {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        };

        const renderNotificationList = (items) => {
            if (!listEl) return;

            if (!items || items.length === 0) {
                listEl.innerHTML = `
                    <div class="p-4 text-center text-muted small">
                        <i class="bi bi-bell-slash fs-4 d-block mb-1 text-secondary opacity-50"></i>
                        <span>Belum ada pemberitahuan baru</span>
                    </div>`;
                return;
            }

            let html = '';
            items.forEach(item => {
                const unreadClass = !item.is_read ? 'bg-light bg-opacity-75 border-start border-3 border-primary' : '';
                const unreadDot = !item.is_read ? '<span class="badge bg-primary rounded-circle p-1" style="width: 8px; height: 8px;" title="Belum dibaca"></span>' : '';
                const targetUrl = item.target_url || `${baseUrl}/notifikasi`;

                html += `
                    <a href="${escapeHtml(targetUrl)}" 
                       class="list-group-item list-group-item-action p-3 text-decoration-none border-bottom notif-item-link ${unreadClass}" 
                       data-notif-id="${item.id}" 
                       data-is-read="${item.is_read ? '1' : '0'}">
                        <div class="d-flex align-items-start gap-2">
                            <div class="bg-${item.badge_color || 'primary'} bg-opacity-10 text-${item.badge_color || 'primary'} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.95rem;">
                                <i class="bi ${item.icon || 'bi-bell-fill'}"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-${item.badge_color || 'secondary'} rounded-pill" style="font-size: 0.65rem;">
                                        ${escapeHtml(item.type)}
                                    </span>
                                    <div class="d-flex align-items-center gap-1">
                                        <small class="text-muted" style="font-size: 0.7rem;">${escapeHtml(item.time_ago)}</small>
                                        ${unreadDot}
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">
                                    ${escapeHtml(item.title)}
                                </h6>
                                <p class="text-muted mb-0 small text-truncate" style="font-size: 0.775rem;">
                                    ${escapeHtml(item.message)}
                                </p>
                            </div>
                        </div>
                    </a>`;
            });

            listEl.innerHTML = html;

            // Bind click to mark as read via AJAX before following link
            listEl.querySelectorAll('.notif-item-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    const isRead = this.getAttribute('data-is-read') === '1';
                    const notifId = this.getAttribute('data-notif-id');
                    if (!isRead && notifId) {
                        // Mark as read in background
                        const formData = new FormData();
                        formData.append('_csrf_token', csrfToken);
                        fetch(`${baseUrl}/api/notifikasi/baca/${notifId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formData
                        }).catch(() => {});
                    }
                });
            });
        };

        const updateBadge = (count) => {
            if (!badgeEl) return;
            const numericCount = parseInt(count, 10) || 0;
            if (numericCount > 0) {
                badgeEl.textContent = numericCount > 99 ? '99+' : numericCount;
                badgeEl.classList.remove('d-none');
            } else {
                badgeEl.classList.add('d-none');
                badgeEl.textContent = '0';
            }
        };

        const fetchNotifications = async () => {
            try {
                const response = await fetch(`${baseUrl}/api/notifikasi/summary`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) return;

                const data = await response.json();
                if (data.logged_in) {
                    updateBadge(data.unread_count);
                    renderNotificationList(data.latest);
                }
            } catch (err) {
                // Network or silent error, do not disrupt UI
            }
        };

        // Mark all read button handler
        if (btnMarkAllRead) {
            btnMarkAllRead.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    btnMarkAllRead.disabled = true;
                    const formData = new FormData();
                    formData.append('_csrf_token', csrfToken);

                    const resp = await fetch(`${baseUrl}/api/notifikasi/baca-semua`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });
                    if (resp.ok) {
                        updateBadge(0);
                        // Visually clear unread marks in dropdown
                        if (listEl) {
                            listEl.querySelectorAll('.notif-item-link').forEach(link => {
                                link.classList.remove('bg-light', 'bg-opacity-75', 'border-start', 'border-3', 'border-primary');
                                link.setAttribute('data-is-read', '1');
                                const dot = link.querySelector('.badge.bg-primary.rounded-circle');
                                if (dot) dot.remove();
                            });
                        }
                    }
                } catch (err) {
                    console.error('Error marking all as read:', err);
                } finally {
                    btnMarkAllRead.disabled = false;
                }
            });
        }

        // Start polling when page is visible
        const startPolling = () => {
            if (pollTimer) clearInterval(pollTimer);
            fetchNotifications();
            pollTimer = setInterval(() => {
                if (!document.hidden) {
                    fetchNotifications();
                }
            }, POLL_INTERVAL);
        };

        const stopPolling = () => {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        };

        // Tab visibility listeners
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopPolling();
            } else {
                startPolling();
            }
        });

        window.addEventListener('focus', () => {
            fetchNotifications();
        });

        // Initial fetch and start timer
        startPolling();
    }
});

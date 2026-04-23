const state = {
    user: null,
    dashboard: null,
    currentView: window.secretaryDashboardConfig?.initialView || "dashboard",
    selectedSlot: null,
    selectedQueueDoctorId: "",
    refreshTimer: null,
};

const els = {};

document.addEventListener("DOMContentLoaded", () => {
    syncTheme();
    cacheElements();
    bindEvents();
    setCurrentDate();
    bootstrapDashboard();
});

function syncTheme() {
    const theme = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", theme);
    document.body?.setAttribute("data-theme", theme);
}

function cacheElements() {
    [
        "view-title",
        "current-date",
        "sidebar-secretary-avatar",
        "sidebar-secretary-name",
        "sidebar-secretary-meta",
        "notification",
        "notification-text",
        "overlay",
        "sidebar",
        "hamburger-btn",
        "open-patient-view-btn",
        "open-appointment-view-btn",
        "secretary-stat-total",
        "secretary-stat-waiting",
        "secretary-stat-completed",
        "secretary-stat-doctors",
        "secretary-notification-feed",
        "secretary-queue-toolbar",
        "secretary-queue-doctor-select",
        "secretary-queue-apply-btn",
        "secretary-dashboard-panels",
        "secretary-queue-panels",
        "secretary-patient-form",
        "secretary-patient-reset-btn",
        "secretary-patient-list",
        "secretary-appointment-form",
        "secretary-appointment-reset-btn",
        "secretary-appointment-patient",
        "secretary-appointment-doctor",
        "secretary-appointment-date",
        "secretary-available-slots",
        "secretary-appointment-reason",
        "secretary-upcoming-appointments",
    ].forEach((id) => {
        els[toCamel(id)] = document.getElementById(id);
    });

    els.navItems = Array.from(document.querySelectorAll(".nav-item"));
    els.viewContainers = Array.from(document.querySelectorAll(".view-container"));
}

function bindEvents() {
    els.navItems.forEach((item) => {
        item.addEventListener("click", () => switchView(item.dataset.view));
    });

    els.hamburgerBtn.addEventListener("click", () => {
        els.sidebar.classList.add("active");
        els.overlay.classList.add("active");
    });

    els.overlay.addEventListener("click", closeSidebar);
    els.openPatientViewBtn.addEventListener("click", () => switchView("patients"));
    els.openAppointmentViewBtn.addEventListener("click", () => switchView("appointments"));
    els.secretaryPatientForm.addEventListener("submit", submitPatientForm);
    els.secretaryPatientResetBtn.addEventListener("click", () => els.secretaryPatientForm.reset());
    els.secretaryAppointmentForm.addEventListener("submit", submitAppointmentForm);
    els.secretaryAppointmentResetBtn.addEventListener("click", resetAppointmentForm);
    els.secretaryAppointmentDoctor.addEventListener("change", loadSecretarySlots);
    els.secretaryAppointmentDate.addEventListener("change", loadSecretarySlots);
    els.secretaryQueueApplyBtn.addEventListener("click", applyQueueDoctorFilter);
}

async function bootstrapDashboard() {
    try {
        const user = await ensureSecretaryAccess();

        if (!user) {
            return;
        }

        state.user = user;
        await refreshDashboard();
        switchView(state.currentView);
        startAutoRefresh();
        showNotification("Le dashboard secretaire est synchronise.");
    } catch (error) {
        handleApiError(error);
    }
}

async function ensureSecretaryAccess() {
    let user = typeof getAuthData === "function" ? getAuthData().user : null;

    if (!user && typeof getCurrentUser === "function") {
        user = await getCurrentUser();
    }

    if (!user) {
        window.location.href = "/connexion";
        return null;
    }

    if (user.role !== "SECRETAIRE") {
        window.location.href = typeof resolveDashboardPath === "function"
            ? resolveDashboardPath(user)
            : "/dashboard";
        return null;
    }

    return user;
}

async function refreshDashboard(options = {}) {
    const { refreshSlots = true } = options;
    const response = await apiCall("/secretary/dashboard-data");
    const payload = response.data || response;
    applyDashboardData(payload);

    if (refreshSlots) {
        await loadSecretarySlots();
    }
}

function applyDashboardData(data) {
    state.dashboard = data;
    renderProfile();
    renderStats();
    populatePatientOptions();
    populateDoctorOptions();
    populateQueueDoctorOptions();
    renderNotifications();
    renderDoctorBoards();
    renderPatientList();
    renderUpcomingAppointments();

    if (!els.secretaryAppointmentDate.value) {
        els.secretaryAppointmentDate.value = tomorrowDateKey();
    }
}

function renderProfile() {
    const secretary = state.dashboard?.secretary;

    if (!secretary) {
        return;
    }

    els.sidebarSecretaryAvatar.textContent = secretary.initials || "SC";
    els.sidebarSecretaryName.textContent = secretary.full_name || "Secretaire";
    els.sidebarSecretaryMeta.textContent = `${secretary.assignment || "Secretaire"} - ${secretary.office_number || "--"}`;
}

function renderStats() {
    const stats = state.dashboard?.stats || {};

    els.secretaryStatTotal.textContent = String(stats.appointments_today || 0);
    els.secretaryStatWaiting.textContent = String(stats.waiting_today || 0);
    els.secretaryStatCompleted.textContent = String(stats.completed_today || 0);
    els.secretaryStatDoctors.textContent = String(stats.active_doctors || 0);
}

function populatePatientOptions() {
    const options = state.dashboard?.patient_options || [];

    els.secretaryAppointmentPatient.innerHTML = options.length
        ? options.map((patient) => `
            <option value="${patient.id}">
                ${escapeHtml(patient.label)} - ${escapeHtml(patient.dossier)} - ${escapeHtml(patient.email)}
            </option>
        `).join("")
        : '<option value="">Aucun patient disponible</option>';
}

function populateDoctorOptions() {
    const options = state.dashboard?.doctor_options || [];

    els.secretaryAppointmentDoctor.innerHTML = options.length
        ? options.map((doctor) => `
            <option value="${doctor.id}">
                ${escapeHtml(doctor.label)}
            </option>
        `).join("")
        : '<option value="">Aucun medecin disponible</option>';
}

function populateQueueDoctorOptions() {
    const options = state.dashboard?.doctor_options || [];
    const currentValue = state.selectedQueueDoctorId;

    els.secretaryQueueDoctorSelect.innerHTML = [
        '<option value="">Tous les medecins actifs</option>',
        ...options.map((doctor) => `
            <option value="${doctor.id}" ${String(doctor.id) === String(currentValue) ? "selected" : ""}>
                ${escapeHtml(doctor.label)}
            </option>
        `),
    ].join("");
}

function renderDoctorBoards() {
    const panels = getVisibleDoctorPanels();
    const html = panels.length
        ? panels.map((panel) => `
            <article class="secretary-doctor-card">
                <div class="queue-header">
                    <div>
                        <h3>${escapeHtml(panel.doctor_name)}</h3>
                        <p class="secretary-doctor-subtitle">${escapeHtml(panel.specialization)} - ${escapeHtml(panel.location)}</p>
                    </div>
                    <div class="queue-count">${panel.waiting_count} en attente</div>
                </div>

                <div class="secretary-queue-summary">
                    <div class="record-detail">
                        <span>Patient courant</span>
                        <strong>${escapeHtml(panel.current_consultation?.turn || "--")}</strong>
                    </div>
                    <div class="record-detail">
                        <span>Patient suivant</span>
                        <strong>${escapeHtml(panel.next_consultation?.turn || "--")}</strong>
                    </div>
                    <div class="record-detail">
                        <span>Reste a traiter</span>
                        <strong>${escapeHtml(String(panel.remaining_after_current || 0))}</strong>
                    </div>
                </div>

                <div class="secretary-current-card">
                    <span class="about-badge">Current Consultation</span>
                    ${panel.current_consultation ? `
                        <h4>${escapeHtml(panel.current_consultation.patient_name)}</h4>
                        <p>${escapeHtml(panel.current_consultation.turn)} - ${escapeHtml(panel.current_consultation.time)} - ${escapeHtml(panel.current_consultation.reason)}</p>
                        <p>Dossier: ${escapeHtml(panel.current_consultation.patient_dossier)}</p>
                    ` : '<p>Aucune consultation en cours pour ce medecin.</p>'}
                </div>

                <div class="secretary-next-card">
                    <span class="about-badge">Patient suivant</span>
                    ${panel.next_consultation ? `
                        <h4>${escapeHtml(panel.next_consultation.patient_name)}</h4>
                        <p>${escapeHtml(panel.next_consultation.turn)} - ${escapeHtml(panel.next_consultation.time)} - ${escapeHtml(panel.next_consultation.reason)}</p>
                        <p>Dossier: ${escapeHtml(panel.next_consultation.patient_dossier)}</p>
                    ` : '<p>Aucun patient suivant pour ce medecin.</p>'}
                </div>

                <div class="queue-list secretary-queue-list">
                    ${(panel.queue || []).length
                        ? panel.queue.map((item) => `
                            <div class="queue-item${panel.current_consultation?.appointment_id === item.appointment_id ? " active" : ""}">
                                <div class="queue-avatar">${escapeHtml(item.patient_initials)}</div>
                                <div class="queue-info">
                                    <h4>${escapeHtml(item.patient_name)}</h4>
                                    <p>${escapeHtml(item.turn)} - ${escapeHtml(item.time)} - ${escapeHtml(item.reason)}</p>
                                </div>
                            </div>
                        `).join("")
                        : emptyBlock("Aucun patient dans la file de ce medecin.")}
                </div>
            </article>
        `).join("")
        : emptyBlock("Aucune file active pour le moment.");

    els.secretaryDashboardPanels.innerHTML = html;
    els.secretaryQueuePanels.innerHTML = html;
}

function renderNotifications() {
    const notifications = state.dashboard?.notifications?.items || [];

    els.secretaryNotificationFeed.innerHTML = notifications.length
        ? notifications.map((item) => `
            <article class="patient-note-card">
                <strong>${escapeHtml(item.content)}</strong>
                <span>${escapeHtml(formatDateTime(item.date))}</span>
            </article>
        `).join("")
        : emptyBlock("Aucune notification secretaire pour le moment.");
}

function getVisibleDoctorPanels() {
    const panels = state.dashboard?.doctor_panels || [];

    if (state.selectedQueueDoctorId) {
        return panels.filter((panel) => String(panel.doctor_id) === String(state.selectedQueueDoctorId));
    }

    const activePanels = panels.filter((panel) => panel.has_activity);
    return activePanels.length ? activePanels : panels;
}

function applyQueueDoctorFilter() {
    state.selectedQueueDoctorId = els.secretaryQueueDoctorSelect.value || "";
    renderDoctorBoards();
}

function renderPatientList() {
    const patients = state.dashboard?.patients || [];

    els.secretaryPatientList.innerHTML = patients.length
        ? patients.map((patient) => `
            <div class="patient-list-item">
                <div class="patient-list-name">${escapeHtml(patient.name)}</div>
                <div class="patient-list-details">
                    <span>${escapeHtml(patient.dossier)}</span>
                    <span>${escapeHtml(patient.age)} - ${escapeHtml(patient.gender)}</span>
                </div>
                <div class="patient-list-details">
                    <span>${escapeHtml(patient.email)}</span>
                    <span>${escapeHtml(patient.telephone)}</span>
                </div>
            </div>
        `).join("")
        : emptyBlock("Aucun patient enregistre.");
}

function renderUpcomingAppointments() {
    const appointments = state.dashboard?.appointments || [];

    els.secretaryUpcomingAppointments.innerHTML = appointments.length
        ? appointments.map((appointment) => `
            <article class="appointment-item patient-appointment-item">
                <div class="appointment-time">
                    <i class="fas fa-clock"></i>
                    ${escapeHtml(appointment.time)}
                </div>
                <div class="appointment-patient">${escapeHtml(appointment.patient)}</div>
                <div class="appointment-type">${escapeHtml(appointment.doctor)} - ${escapeHtml(appointment.date)} - ${escapeHtml(appointment.reason)}</div>
            </article>
        `).join("")
        : emptyBlock("Aucun rendez-vous a venir.");
}

async function loadSecretarySlots() {
    const doctorId = els.secretaryAppointmentDoctor.value;
    const date = els.secretaryAppointmentDate.value;

    state.selectedSlot = null;

    if (!doctorId || !date) {
        els.secretaryAvailableSlots.innerHTML = emptyBlock("Choisissez un medecin puis une date.");
        return;
    }

    try {
        const response = await apiCall(`/secretary/doctors/${doctorId}/available-slots?date=${encodeURIComponent(date)}`);
        const slots = response.data || response;

        els.secretaryAvailableSlots.innerHTML = slots.length
            ? slots.map((slot) => `
                <button type="button" class="slot-chip" data-secretary-slot="${slot.value}">
                    ${escapeHtml(slot.label)}
                </button>
            `).join("")
            : emptyBlock("Aucun creneau libre sur cette date. Les rendez-vous sont proposes du lundi au vendredi, hors pause 13:00-14:00.");

        els.secretaryAvailableSlots.querySelectorAll("[data-secretary-slot]").forEach((button) => {
            button.addEventListener("click", () => {
                state.selectedSlot = button.dataset.secretarySlot;
                els.secretaryAvailableSlots.querySelectorAll("[data-secretary-slot]").forEach((item) => {
                    item.classList.toggle("active", item.dataset.secretarySlot === state.selectedSlot);
                });
            });
        });
    } catch (error) {
        handleApiError(error);
    }
}

async function submitPatientForm(event) {
    event.preventDefault();

    try {
        const formData = Object.fromEntries(new FormData(els.secretaryPatientForm).entries());
        const response = await apiCall("/secretary/patients", "POST", formData);

        els.secretaryPatientForm.reset();
        applyDashboardData(response.data || response);
        switchView("patients");
        showNotification("Le patient a ete enregistre avec succes.");
    } catch (error) {
        handleApiError(error);
    }
}

async function submitAppointmentForm(event) {
    event.preventDefault();

    if (!state.selectedSlot) {
        showNotification("Veuillez choisir un creneau disponible avant de valider.", "error");
        return;
    }

    try {
        const response = await apiCall("/secretary/appointments", "POST", {
            patient_id: Number(els.secretaryAppointmentPatient.value),
            doctor_id: Number(els.secretaryAppointmentDoctor.value),
            appointment_date: state.selectedSlot,
            reason: els.secretaryAppointmentReason.value,
        });

        resetAppointmentForm();
        applyDashboardData(response.data || response);
        await loadSecretarySlots();
        switchView("appointments");
        showNotification("Le rendez-vous a ete cree avec succes.");
    } catch (error) {
        handleApiError(error);
    }
}

function resetAppointmentForm() {
    state.selectedSlot = null;
    els.secretaryAppointmentReason.value = "";
    els.secretaryAppointmentDate.value = tomorrowDateKey();
    loadSecretarySlots().catch(handleApiError);
}

function switchView(viewName) {
    state.currentView = viewName;
    els.navItems.forEach((item) => item.classList.toggle("active", item.dataset.view === viewName));
    els.viewContainers.forEach((container) => container.classList.toggle("active", container.id === `${viewName}-view`));
    els.viewTitle.textContent = {
        dashboard: "Dashboard",
        patients: "Patients",
        appointments: "Rendez-vous",
        queues: "Patient Queue",
    }[viewName] || "Dashboard";
    els.secretaryQueueToolbar.hidden = !["dashboard", "queues"].includes(viewName);
    closeSidebar();
}

function closeSidebar() {
    els.sidebar.classList.remove("active");
    els.overlay.classList.remove("active");
}

function setCurrentDate() {
    els.currentDate.textContent = new Date().toLocaleDateString("fr-FR", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function showNotification(message, variant = "success") {
    els.notificationText.textContent = message;
    els.notification.classList.toggle("is-error", variant === "error");
    els.notification.style.display = "flex";
    window.clearTimeout(showNotification.timer);
    showNotification.timer = window.setTimeout(() => {
        els.notification.style.display = "none";
    }, 3000);
}

function handleApiError(error) {
    if (error?.status === 401) {
        window.location.href = "/connexion";
        return;
    }

    if (error?.status === 403 && state.user) {
        window.location.href = typeof resolveDashboardPath === "function"
            ? resolveDashboardPath(state.user)
            : "/dashboard";
        return;
    }

    showNotification(error?.message || "Une erreur est survenue.", "error");
}

function tomorrowDateKey() {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    date.setHours(0, 0, 0, 0);

    while (date.getDay() === 0 || date.getDay() === 6) {
        date.setDate(date.getDate() + 1);
    }

    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
}

function startAutoRefresh() {
    if (state.refreshTimer) {
        window.clearInterval(state.refreshTimer);
    }

    state.refreshTimer = window.setInterval(async () => {
        if (document.hidden || !["dashboard", "queues"].includes(state.currentView)) {
            return;
        }

        try {
            await refreshDashboard({ refreshSlots: false });
        } catch (error) {
            console.warn("Secretary auto refresh warning:", error);
        }
    }, 30000);
}

function emptyBlock(text) {
    return `<div class="empty-state"><p>${escapeHtml(text)}</p></div>`;
}

function formatDateTime(value) {
    if (!value) {
        return "--";
    }

    return new Date(value).toLocaleString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function toCamel(text) {
    return text.replace(/-([a-z])/g, (_, letter) => letter.toUpperCase());
}

function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

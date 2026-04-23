const state = {
    user: null,
    dashboard: null,
    currentView: window.patientDashboardConfig?.initialView || "dashboard",
    calendarDate: new Date(),
    selectedDate: null,
    selectedDoctorId: null,
    selectedSlot: null,
    editingAppointmentId: null,
    availabilityCalendar: {},
    availableSlots: [],
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
        "sidebar-patient-avatar",
        "sidebar-patient-name",
        "sidebar-patient-meta",
        "notification",
        "notification-text",
        "overlay",
        "sidebar",
        "hamburger-btn",
        "create-appointment-header-btn",
        "next-appointment-card",
        "next-appointment-date",
        "next-appointment-reason",
        "next-appointment-turn",
        "next-appointment-time",
        "next-appointment-doctor",
        "next-appointment-location",
        "next-appointment-specialization",
        "next-appointment-message",
        "stat-age",
        "stat-dossier",
        "stat-consultations",
        "stat-ordonnances",
        "patient-health-summary",
        "patient-notification-list",
        "patient-appointment-doctor",
        "patient-prev-month",
        "patient-next-month",
        "patient-today-btn",
        "patient-current-month",
        "patient-calendar-days",
        "patient-selected-date-label",
        "patient-available-slots",
        "patient-appointment-reason",
        "patient-appointment-form",
        "patient-appointment-submit",
        "patient-appointment-mode",
        "reset-patient-appointment-form-btn",
        "patient-appointment-cancel-btn",
        "patient-upcoming-appointments",
        "patient-appointment-history",
        "patient-personal-info",
        "patient-dossier-summary",
        "patient-consultation-list",
        "patient-ordonnance-list",
        "patient-ordonnance-modal",
        "patient-ordonnance-modal-title",
        "patient-ordonnance-modal-body",
        "close-patient-ordonnance-modal-btn",
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
    els.createAppointmentHeaderBtn.addEventListener("click", () => {
        switchView("appointments");
        els.patientAppointmentDoctor.focus();
    });
    els.patientAppointmentDoctor.addEventListener("change", async () => {
        state.selectedDoctorId = Number(els.patientAppointmentDoctor.value) || null;
        state.selectedSlot = null;
        await refreshAvailabilityData();
    });
    els.patientPrevMonth.addEventListener("click", async () => {
        state.calendarDate = new Date(state.calendarDate.getFullYear(), state.calendarDate.getMonth() - 1, 1);
        await refreshAvailabilityCalendar();
        renderCalendar();
    });
    els.patientNextMonth.addEventListener("click", async () => {
        state.calendarDate = new Date(state.calendarDate.getFullYear(), state.calendarDate.getMonth() + 1, 1);
        await refreshAvailabilityCalendar();
        renderCalendar();
    });
    els.patientTodayBtn.addEventListener("click", async () => {
        const today = new Date();
        state.calendarDate = new Date(today.getFullYear(), today.getMonth(), 1);
        state.selectedDate = today;
        state.selectedSlot = null;
        await refreshAvailabilityData();
    });
    els.patientAppointmentForm.addEventListener("submit", submitAppointmentForm);
    els.resetPatientAppointmentFormBtn.addEventListener("click", resetAppointmentComposer);
    els.patientAppointmentCancelBtn.addEventListener("click", resetAppointmentComposer);
    els.closePatientOrdonnanceModalBtn.addEventListener("click", closeOrdonnanceModal);
}

async function bootstrapDashboard() {
    try {
        const user = await ensurePatientAccess();

        if (!user) {
            return;
        }

        state.user = user;
        await refreshDashboard();
        switchView(state.currentView);
        showNotification("Votre espace patient est synchronise.");
    } catch (error) {
        handleApiError(error);
    }
}

async function ensurePatientAccess() {
    let user = typeof getAuthData === "function" ? getAuthData().user : null;

    if (!user && typeof getCurrentUser === "function") {
        user = await getCurrentUser();
    }

    if (!user) {
        window.location.href = "/connexion";
        return null;
    }

    if (user.role !== "PATIENT") {
        window.location.href = typeof resolveDashboardPath === "function"
            ? resolveDashboardPath(user)
            : "/dashboard";
        return null;
    }

    return user;
}

async function refreshDashboard() {
    const response = await apiCall("/patient/dashboard-data");
    const payload = response.data || response;
    await applyDashboardData(payload);
}

async function applyDashboardData(data) {
    state.dashboard = data;

    if (!state.selectedDoctorId) {
        state.selectedDoctorId = data.next_appointment?.appointment_id
            ? data.appointments?.upcoming?.find((item) => item.id === data.next_appointment.appointment_id)?.doctor_id || data.doctor_options?.[0]?.id || null
            : data.doctor_options?.[0]?.id || null;
    }

    if (!state.selectedDate) {
        const nextDate = data.next_appointment?.date ? new Date(`${data.next_appointment.date}T00:00:00`) : tomorrowAtMidnight();
        state.selectedDate = nextDate;
        state.calendarDate = new Date(nextDate.getFullYear(), nextDate.getMonth(), 1);
    }

    renderAll();
    await refreshAvailabilityData();
}

function renderAll() {
    renderProfile();
    renderNextAppointment();
    renderStats();
    renderHealthSummary();
    renderNotifications();
    populateDoctorOptions();
    renderAppointmentLists();
    renderPersonalInfo();
    renderConsultations();
    renderOrdonnances();
}

function renderProfile() {
    const patient = state.dashboard?.patient;

    if (!patient) {
        return;
    }

    els.sidebarPatientAvatar.textContent = patient.initials || "PT";
    els.sidebarPatientName.textContent = patient.full_name || "Patient";
    els.sidebarPatientMeta.textContent = `${patient.age_label || "--"} - ${patient.dossier || "--"}`;
}

function renderNextAppointment() {
    const appointment = state.dashboard?.next_appointment;

    if (!appointment) {
        els.nextAppointmentDate.textContent = "Aucun rendez-vous planifie";
        els.nextAppointmentReason.textContent = "Utilisez l onglet rendez-vous pour reserver votre prochain creneau.";
        els.nextAppointmentTurn.textContent = "--";
        els.nextAppointmentTime.textContent = "--:--";
        els.nextAppointmentDoctor.textContent = "--";
        els.nextAppointmentLocation.textContent = "--";
        els.nextAppointmentSpecialization.textContent = "--";
        els.nextAppointmentMessage.textContent = "Votre numero de tour apparaitra ici des qu un rendez-vous sera confirme.";
        return;
    }

    els.nextAppointmentDate.textContent = appointment.date_label;
    els.nextAppointmentReason.textContent = appointment.reason;
    els.nextAppointmentTurn.textContent = appointment.turn;
    els.nextAppointmentTime.textContent = appointment.time;
    els.nextAppointmentDoctor.textContent = appointment.doctor;
    els.nextAppointmentLocation.textContent = appointment.location;
    els.nextAppointmentSpecialization.textContent = appointment.specialization;
    els.nextAppointmentMessage.textContent = appointment.message;
}

function renderStats() {
    const patient = state.dashboard?.patient || {};
    const stats = state.dashboard?.stats || {};

    els.statAge.textContent = patient.age_label || "--";
    els.statDossier.textContent = stats.active_dossier || patient.dossier || "--";
    els.statConsultations.textContent = String(stats.consultations || 0);
    els.statOrdonnances.textContent = String(stats.ordonnances || 0);
}

function renderHealthSummary() {
    const summary = state.dashboard?.records?.summary || {};

    els.patientHealthSummary.innerHTML = Object.entries(summary).length
        ? Object.entries(summary).map(([label, value]) => `
            <div class="record-detail">
                <span>${escapeHtml(label)}</span>
                <strong>${escapeHtml(value)}</strong>
            </div>
        `).join("")
        : emptyBlock("Aucune information medicale disponible.");
}

function renderNotifications() {
    const notifications = state.dashboard?.notifications?.items || [];

    els.patientNotificationList.innerHTML = notifications.length
        ? notifications.map((item) => `
            <article class="patient-note-card">
                <strong>${escapeHtml(item.content)}</strong>
                <span>${escapeHtml(formatDateTime(item.date))}</span>
            </article>
        `).join("")
        : emptyBlock("Aucune notification pour le moment.");
}

function populateDoctorOptions() {
    const options = state.dashboard?.doctor_options || [];

    els.patientAppointmentDoctor.innerHTML = options.length
        ? options.map((doctor) => `
            <option value="${doctor.id}" ${Number(doctor.id) === Number(state.selectedDoctorId) ? "selected" : ""}>
                ${escapeHtml(doctor.label)}
            </option>
        `).join("")
        : '<option value="">Aucun medecin disponible</option>';
}

async function refreshAvailabilityData() {
    if (!state.selectedDoctorId) {
        state.availabilityCalendar = {};
        state.availableSlots = [];
        renderCalendar();
        renderAvailableSlots();
        return;
    }

    await Promise.all([
        refreshAvailabilityCalendar(),
        refreshAvailableSlots(),
    ]);

    renderCalendar();
    renderAvailableSlots();
    updateAppointmentMode();
}

async function refreshAvailabilityCalendar() {
    const response = await apiCall(`/patient/doctors/${state.selectedDoctorId}/availability-calendar?month=${monthKey(state.calendarDate)}${state.editingAppointmentId ? `&ignore_appointment_id=${state.editingAppointmentId}` : ""}`);
    state.availabilityCalendar = response.data || response;
}

async function refreshAvailableSlots() {
    const selectedDate = state.selectedDate || tomorrowAtMidnight();
    const response = await apiCall(`/patient/doctors/${state.selectedDoctorId}/available-slots?date=${dateKey(selectedDate)}${state.editingAppointmentId ? `&ignore_appointment_id=${state.editingAppointmentId}` : ""}`);
    state.availableSlots = response.data || response;

    if (state.selectedSlot && !state.availableSlots.some((slot) => slot.value === state.selectedSlot)) {
        state.selectedSlot = null;
    }
}

function renderCalendar() {
    const date = state.calendarDate;
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    els.patientCurrentMonth.textContent = date.toLocaleDateString("fr-FR", {
        month: "long",
        year: "numeric",
    });
    els.patientCalendarDays.innerHTML = "";

    for (let i = 0; i < firstDay.getDay(); i += 1) {
        const placeholder = document.createElement("div");
        placeholder.className = "calendar-day placeholder";
        els.patientCalendarDays.appendChild(placeholder);
    }

    for (let day = 1; day <= lastDay.getDate(); day += 1) {
        const cellDate = new Date(year, month, day);
        const key = dateKey(cellDate);
        const availability = state.availabilityCalendar?.[key] || { count: 0, is_selectable: false };
        const selectable = Boolean(availability.is_selectable && availability.count > 0);
        const cell = document.createElement("button");

        cell.type = "button";
        cell.className = "calendar-day patient-calendar-day";
        cell.innerHTML = `
            <span class="day-number">${day}</span>
            <span class="day-meta">${availability.count > 0 ? `${availability.count} slot${availability.count > 1 ? "s" : ""}` : "Complet"}</span>
        `;

        if (isSameDay(cellDate, new Date())) {
            cell.classList.add("today");
        }

        if (availability.count > 0) {
            cell.classList.add("appointment");
        }

        if (isSameDay(cellDate, state.selectedDate)) {
            cell.classList.add("selected");
        }

        if (!selectable) {
            cell.classList.add("unavailable");
            cell.disabled = true;
        } else {
            cell.addEventListener("click", async () => {
                state.selectedDate = cellDate;
                state.selectedSlot = null;
                await refreshAvailableSlots();
                renderCalendar();
                renderAvailableSlots();
            });
        }

        els.patientCalendarDays.appendChild(cell);
    }
}

function renderAvailableSlots() {
    const selectedDate = state.selectedDate || tomorrowAtMidnight();

    els.patientSelectedDateLabel.textContent = `Date selectionnee : ${formatLongDate(selectedDate)}${state.editingAppointmentId ? " (modification)" : ""} - Horaires disponibles : 08:00-12:00 et 14:00-16:00, du lundi au vendredi.`;
    els.patientAvailableSlots.innerHTML = state.availableSlots.length
        ? state.availableSlots.map((slot) => `
            <button type="button" class="slot-chip${slot.value === state.selectedSlot ? " active" : ""}" data-slot-value="${slot.value}">
                ${escapeHtml(slot.label)}
            </button>
        `).join("")
        : emptyBlock("Aucun creneau libre sur cette date pour ce medecin. Les rendez-vous sont proposes du lundi au vendredi, hors pause 13:00-14:00.");

    els.patientAvailableSlots.querySelectorAll("[data-slot-value]").forEach((button) => {
        button.addEventListener("click", () => {
            state.selectedSlot = button.dataset.slotValue;
            renderAvailableSlots();
        });
    });
}

function renderAppointmentLists() {
    const upcoming = state.dashboard?.appointments?.upcoming || [];
    const history = state.dashboard?.appointments?.history || [];

    els.patientUpcomingAppointments.innerHTML = upcoming.length
        ? upcoming.map((appointment) => `
            <article class="appointment-item patient-appointment-item">
                <div class="appointment-time">
                    <i class="fas fa-clock"></i>
                    ${escapeHtml(appointment.time)}
                </div>
                <div class="appointment-patient">${escapeHtml(appointment.doctor)}</div>
                <div class="appointment-type">${escapeHtml(appointment.reason)} - ${escapeHtml(appointment.date_label)} - ${escapeHtml(appointment.location)}</div>
                <div class="patient-appointment-actions">
                    <span class="consultation-status">${escapeHtml(appointment.turn)}</span>
                    ${appointment.modifiable ? `<button class="btn btn-secondary patient-inline-btn" type="button" data-edit-appointment-id="${appointment.id}">Modifier</button>` : ""}
                </div>
            </article>
        `).join("")
        : emptyBlock("Aucun rendez-vous a venir.");

    els.patientAppointmentHistory.innerHTML = history.length
        ? history.map((appointment) => `
            <article class="appointment-item patient-appointment-item">
                <div class="appointment-time">
                    <i class="fas fa-calendar-day"></i>
                    ${escapeHtml(appointment.date_label)}
                </div>
                <div class="appointment-patient">${escapeHtml(appointment.doctor)}</div>
                <div class="appointment-type">${escapeHtml(appointment.reason)} - ${escapeHtml(appointment.status_label)}</div>
            </article>
        `).join("")
        : emptyBlock("Aucun historique de rendez-vous.");

    els.patientUpcomingAppointments.querySelectorAll("[data-edit-appointment-id]").forEach((button) => {
        button.addEventListener("click", async () => {
            await prepareAppointmentEdition(Number(button.dataset.editAppointmentId));
        });
    });
}

function renderPersonalInfo() {
    const personal = state.dashboard?.records?.personal || {};
    const summary = state.dashboard?.records?.summary || {};

    els.patientPersonalInfo.innerHTML = Object.entries(personal).length
        ? Object.entries(personal).map(([label, value]) => `
            <div class="patient-info-row">
                <span>${escapeHtml(label)}</span>
                <strong>${escapeHtml(value)}</strong>
            </div>
        `).join("")
        : emptyBlock("Aucune information personnelle disponible.");

    els.patientDossierSummary.innerHTML = Object.entries(summary).length
        ? Object.entries(summary).map(([label, value]) => `
            <div class="patient-info-row">
                <span>${escapeHtml(label)}</span>
                <strong>${escapeHtml(value)}</strong>
            </div>
        `).join("")
        : emptyBlock("Aucun resume medical disponible.");
}

function renderConsultations() {
    const consultations = state.dashboard?.consultations || [];

    els.patientConsultationList.innerHTML = consultations.length
        ? consultations.map((consultation) => `
            <article class="consultation-item">
                <h3>${escapeHtml(consultation.diagnosis)}</h3>
                <div class="consultation-meta">
                    <span><strong>Date :</strong> ${escapeHtml(consultation.date)}</span>
                    <span><strong>Medecin :</strong> ${escapeHtml(consultation.doctor)}</span>
                </div>
                <p class="consultation-notes">${escapeHtml(consultation.treatment)}</p>
                <p class="consultation-notes">${escapeHtml(consultation.notes)}</p>
            </article>
        `).join("")
        : emptyBlock("Aucune consultation a afficher.");
}

function renderOrdonnances() {
    const ordonnances = state.dashboard?.ordonnances || [];

    els.patientOrdonnanceList.innerHTML = ordonnances.length
        ? ordonnances.map((ordonnance) => `
            <article class="consultation-item">
                <h3>${escapeHtml(ordonnance.doctor)}</h3>
                <div class="consultation-meta">
                    <span><strong>Date :</strong> ${escapeHtml(ordonnance.date)}</span>
                    <span><strong>Diagnostic :</strong> ${escapeHtml(ordonnance.diagnosis)}</span>
                </div>
                <p class="consultation-notes">${escapeHtml(ordonnance.details)}</p>
                <div class="patient-appointment-actions">
                    <button class="btn btn-secondary patient-inline-btn" type="button" data-ordonnance-action="view" data-ordonnance-id="${ordonnance.id}">Voir</button>
                    <button class="btn btn-secondary patient-inline-btn" type="button" data-ordonnance-action="download" data-ordonnance-id="${ordonnance.id}">Telecharger</button>
                    <button class="btn btn-secondary patient-inline-btn" type="button" data-ordonnance-action="print" data-ordonnance-id="${ordonnance.id}">Imprimer</button>
                    <button class="btn btn-secondary patient-inline-btn" type="button" data-ordonnance-action="share" data-ordonnance-id="${ordonnance.id}">Partager</button>
                </div>
            </article>
        `).join("")
        : emptyBlock("Aucune ordonnance disponible.");

    els.patientOrdonnanceList.querySelectorAll("[data-ordonnance-action]").forEach((button) => {
        button.addEventListener("click", () => handleOrdonnanceAction(button.dataset.ordonnanceAction, Number(button.dataset.ordonnanceId)));
    });
}

async function prepareAppointmentEdition(appointmentId) {
    const appointment = (state.dashboard?.appointments?.upcoming || []).find((item) => item.id === appointmentId);

    if (!appointment) {
        showNotification("Le rendez-vous selectionne est introuvable.", "error");
        return;
    }

    state.editingAppointmentId = appointmentId;
    state.selectedDoctorId = appointment.doctor_id;
    state.selectedDate = new Date(`${appointment.date}T00:00:00`);
    state.calendarDate = new Date(state.selectedDate.getFullYear(), state.selectedDate.getMonth(), 1);
    state.selectedSlot = isoLocalValue(appointment.datetime);
    els.patientAppointmentReason.value = appointment.reason === "Consultation" ? "" : appointment.reason;

    populateDoctorOptions();
    await refreshAvailabilityData();
    state.selectedSlot = isoLocalValue(appointment.datetime);
    renderAvailableSlots();
    updateAppointmentMode();
    switchView("appointments");
    showNotification("Mode modification active pour ce rendez-vous.");
}

function resetAppointmentComposer() {
    state.editingAppointmentId = null;
    state.selectedSlot = null;
    els.patientAppointmentReason.value = "";
    updateAppointmentMode();
    refreshAvailabilityData().catch(handleApiError);
}

function updateAppointmentMode() {
    const editing = Boolean(state.editingAppointmentId);
    els.patientAppointmentMode.textContent = editing ? "Modifier mon rendez-vous" : "Nouveau rendez-vous";
    els.patientAppointmentSubmit.innerHTML = editing
        ? '<i class="fas fa-pen-to-square"></i> Mettre a jour'
        : '<i class="fas fa-check"></i> Enregistrer';
}

async function submitAppointmentForm(event) {
    event.preventDefault();

    if (!state.selectedDoctorId) {
        showNotification("Veuillez choisir un medecin.", "error");
        return;
    }

    if (!state.selectedSlot) {
        showNotification("Veuillez choisir un creneau disponible avant de confirmer le rendez-vous.", "error");
        return;
    }

    try {
        const endpoint = state.editingAppointmentId
            ? `/patient/appointments/${state.editingAppointmentId}`
            : "/patient/appointments";
        const method = state.editingAppointmentId ? "PATCH" : "POST";
        const response = await apiCall(endpoint, method, {
            doctor_id: state.selectedDoctorId,
            appointment_date: state.selectedSlot,
            reason: els.patientAppointmentReason.value,
        });

        state.editingAppointmentId = null;
        state.selectedSlot = null;
        els.patientAppointmentReason.value = "";
        await applyDashboardData(response.data || response);
        switchView("dashboard");
        showNotification(method === "PATCH" ? "Votre rendez-vous a ete modifie." : "Votre rendez-vous a ete cree.");
    } catch (error) {
        handleApiError(error);
    }
}

function handleOrdonnanceAction(action, ordonnanceId) {
    const ordonnance = (state.dashboard?.ordonnances || []).find((item) => item.id === ordonnanceId);

    if (!ordonnance) {
        showNotification("Ordonnance introuvable.", "error");
        return;
    }

    switch (action) {
        case "view":
            openOrdonnanceModal(ordonnance);
            break;
        case "download":
            downloadOrdonnance(ordonnance);
            break;
        case "print":
            printOrdonnance(ordonnance);
            break;
        case "share":
            shareOrdonnance(ordonnance);
            break;
        default:
            break;
    }
}

function openOrdonnanceModal(ordonnance) {
    els.patientOrdonnanceModalTitle.textContent = `Ordonnance du ${ordonnance.date}`;
    els.patientOrdonnanceModalBody.innerHTML = `
        <div class="patient-ordonnance-sheet">
            <p><strong>Medecin :</strong> ${escapeHtml(ordonnance.doctor)}</p>
            <p><strong>Specialite :</strong> ${escapeHtml(ordonnance.specialization)}</p>
            <p><strong>Diagnostic :</strong> ${escapeHtml(ordonnance.diagnosis)}</p>
            <div class="patient-ordonnance-content">${escapeHtml(ordonnance.details).replace(/\n/g, "<br>")}</div>
        </div>
    `;
    els.patientOrdonnanceModal.hidden = false;
}

function closeOrdonnanceModal() {
    els.patientOrdonnanceModal.hidden = true;
}

function downloadOrdonnance(ordonnance) {
    const html = ordonnanceDocument(ordonnance);
    const blob = new Blob([html], { type: "text/html;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = ordonnance.file_name || `ordonnance-${ordonnance.id}.html`;
    link.click();
    URL.revokeObjectURL(url);
    showNotification("Ordonnance telechargee.");
}

function printOrdonnance(ordonnance) {
    const printWindow = window.open("", "_blank", "width=900,height=700");

    if (!printWindow) {
        showNotification("La fenetre d impression a ete bloquee.", "error");
        return;
    }

    printWindow.document.write(ordonnanceDocument(ordonnance));
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

async function shareOrdonnance(ordonnance) {
    const shareText = `Ordonnance du ${ordonnance.date}\n${ordonnance.doctor}\n${ordonnance.details}`;

    if (navigator.share) {
        try {
            await navigator.share({
                title: `Ordonnance du ${ordonnance.date}`,
                text: shareText,
            });
            return;
        } catch (error) {
            if (error?.name === "AbortError") {
                return;
            }
        }
    }

    await navigator.clipboard.writeText(shareText);
    showNotification("Le contenu de l ordonnance a ete copie dans le presse-papiers.");
}

function ordonnanceDocument(ordonnance) {
    const patient = state.dashboard?.patient || {};

    return `
        <html lang="fr">
            <head>
                <title>Ordonnance ${escapeHtml(ordonnance.date)}</title>
                <style>
                    * { box-sizing: border-box; }
                    body { font-family: Arial, sans-serif; margin: 0; color: #0f172a; background: #ffffff; }
                    .page { padding: 36px 40px; }
                    .sheet { position: relative; min-height: 960px; padding: 38px 42px 28px; border: 1px solid #d8e7f5; border-radius: 24px; overflow: hidden; background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); }
                    .watermark { position: absolute; inset: 50% auto auto 50%; transform: translate(-50%, -50%); font-size: 180px; font-weight: 700; color: rgba(14, 165, 233, 0.06); letter-spacing: 6px; }
                    .header { position: relative; display: flex; justify-content: space-between; align-items: start; gap: 20px; padding-bottom: 20px; margin-bottom: 24px; border-bottom: 2px solid #cfe6f7; }
                    .header h1 { margin: 0 0 6px; font-size: 30px; color: #0f172a; }
                    .header p { margin: 0; color: #475569; }
                    .brand { text-align: right; color: #0369a1; }
                    .brand strong { display: block; font-size: 24px; }
                    .brand span { font-size: 13px; color: #64748b; }
                    .meta { position: relative; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 18px; margin-bottom: 28px; }
                    .line { padding-bottom: 8px; border-bottom: 1px solid #dbeafe; }
                    .line span { display: inline-block; min-width: 90px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
                    .line strong { font-size: 15px; }
                    .line.full { grid-column: 1 / -1; }
                    .rx { position: relative; min-height: 450px; margin-bottom: 36px; padding: 24px 24px 30px 84px; border-radius: 24px; border: 1px solid #d8e7f5; background: rgba(255, 255, 255, 0.92); }
                    .rx-title { position: absolute; left: 26px; top: 18px; font-size: 42px; font-weight: 700; color: #0284c7; }
                    .rx-body { min-height: 370px; line-height: 1.85; font-size: 16px; }
                    .footer { display: flex; justify-content: space-between; align-items: end; gap: 20px; padding-top: 18px; border-top: 2px solid #d8e7f5; color: #475569; font-size: 13px; }
                    .footer strong { display: block; margin-bottom: 4px; color: #0f172a; }
                    .signature { min-width: 220px; padding-top: 40px; border-top: 1px solid #94a3b8; text-align: center; }
                </style>
            </head>
            <body>
                <div class="page">
                    <div class="sheet">
                        <div class="watermark">Rx</div>
                        <div class="header">
                            <div>
                                <h1>${escapeHtml(ordonnance.doctor)}</h1>
                                <p>${escapeHtml(ordonnance.specialization)}</p>
                            </div>
                            <div class="brand">
                                <strong>Medicare</strong>
                                <span>Ordonnance medicale</span>
                            </div>
                        </div>
                        <div class="meta">
                            <div class="line"><span>Patient</span><strong>${escapeHtml(patient.full_name || "Patient Medicare")}</strong></div>
                            <div class="line"><span>Age</span><strong>${escapeHtml(patient.age_label || "Non renseigne")}</strong></div>
                            <div class="line"><span>Date</span><strong>${escapeHtml(ordonnance.date)}</strong></div>
                            <div class="line"><span>Sexe</span><strong>${escapeHtml(patient.gender || "Non renseigne")}</strong></div>
                            <div class="line full"><span>Dossier</span><strong>${escapeHtml(patient.dossier || "--")}</strong></div>
                            <div class="line full"><span>Contact</span><strong>${escapeHtml(patient.telephone || patient.email || "Non renseigne")}</strong></div>
                        </div>
                        <div class="rx">
                            <div class="rx-title">Rx</div>
                            <div class="rx-body">${escapeHtml(ordonnance.details).replace(/\n/g, "<br>")}</div>
                        </div>
                        <div class="footer">
                            <div>
                                <strong>Diagnostic</strong>
                                <span>${escapeHtml(ordonnance.diagnosis)}</span>
                            </div>
                            <div class="signature">
                                <span>Signature et cachet</span>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    `;
}

function switchView(viewName) {
    state.currentView = viewName;
    els.navItems.forEach((item) => item.classList.toggle("active", item.dataset.view === viewName));
    els.viewContainers.forEach((container) => container.classList.toggle("active", container.id === `${viewName}-view`));
    els.viewTitle.textContent = viewLabel(viewName);
    closeSidebar();
}

function viewLabel(viewName) {
    return {
        dashboard: "Dashboard",
        appointments: "Rendez-vous",
        records: "Dossier medical",
        consultations: "Consultations",
        ordonnances: "Ordonnances",
    }[viewName] || "Dashboard";
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

function emptyBlock(text) {
    return `<div class="empty-state"><p>${escapeHtml(text)}</p></div>`;
}

function tomorrowAtMidnight() {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    date.setHours(0, 0, 0, 0);

    while (date.getDay() === 0 || date.getDay() === 6) {
        date.setDate(date.getDate() + 1);
    }

    return date;
}

function monthKey(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
}

function dateKey(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
}

function isoLocalValue(value) {
    const date = new Date(value);
    return `${dateKey(date)}T${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;
}

function formatLongDate(date) {
    return date.toLocaleDateString("fr-FR", {
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
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

function isSameDay(first, second) {
    return first.getDate() === second.getDate()
        && first.getMonth() === second.getMonth()
        && first.getFullYear() === second.getFullYear();
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

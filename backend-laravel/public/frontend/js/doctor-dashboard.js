const state = {
    user: null,
    dashboard: null,
    currentView: window.doctorDashboardConfig?.initialView || "dashboard",
    calendarDate: new Date(),
    selectedCalendarDate: new Date(),
    selectedPatientId: null,
    filteredPatientIds: null,
    selectedAppointmentId: null,
    rescheduleAppointmentId: null,
    ordonnanceVisible: false,
    appointmentLookup: null,
    recordFilters: {
        email: "",
        dossier: "",
    },
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
        "sidebar-doctor-avatar",
        "sidebar-doctor-name",
        "sidebar-doctor-specialization",
        "sidebar-logout-btn",
        "notification",
        "notification-text",
        "overlay",
        "sidebar",
        "hamburger-btn",
        "new-ordonnance-btn",
        "next-patient-btn",
        "prev-patient-btn",
        "next-patient-inline-btn",
        "print-report-btn",
        "print-ordonnance-btn",
        "notifications-btn",
        "current-consultation-badge",
        "current-patient-avatar",
        "current-patient-name",
        "current-patient-turn",
        "current-patient-age",
        "current-patient-condition",
        "current-turn-display",
        "current-consultation-status",
        "current-consultation-time",
        "current-consultation-room",
        "diagnosis",
        "symptoms",
        "treatment",
        "next-visit",
        "consultation-helper-text",
        "ordonnance-panel",
        "save-ordonnance-btn",
        "close-ordonnance-panel-btn",
        "ordonnance-patient-name",
        "ordonnance-patient-dossier",
        "ordonnance-patient-contact",
        "ordonnance-patient-condition",
        "ordonnance-details",
        "medical-notes-form",
        "save-notes-btn",
        "complete-visit-btn",
        "reschedule-btn",
        "patient-queue",
        "queue-count",
        "total-patients",
        "completed-patients",
        "waiting-patients",
        "avg-time",
        "progress-fill",
        "prev-month",
        "next-month",
        "today-btn",
        "current-month",
        "calendar-days",
        "today-appointments-title",
        "today-appointments",
        "upcoming-appointments",
        "add-appointment-btn",
        "appointment-form-panel",
        "close-appointment-form-btn",
        "cancel-appointment-form-btn",
        "appointment-form",
        "appointment-patient-email",
        "search-patient-email-btn",
        "appointment-patient-result",
        "appointment-patient",
        "appointment-date",
        "appointment-reason",
        "patient-search",
        "search-patient-btn",
        "new-patient-btn",
        "patient-list",
        "patient-history-list",
        "new-consultation-btn",
        "consultation-list",
        "export-records-btn",
        "records-search-email",
        "records-search-dossier",
        "clear-records-search-btn",
        "record-grid",
        "new-contact-btn",
        "contact-grid",
        "about-action-btn",
        "reschedule-modal",
        "close-reschedule-modal-btn",
        "cancel-reschedule-btn",
        "reschedule-form",
        "reschedule-date",
        "reschedule-reason",
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
    els.prevPatientBtn.addEventListener("click", goToPreviousPatient);
    els.nextPatientBtn.addEventListener("click", goToNextPatient);
    els.nextPatientInlineBtn.addEventListener("click", goToNextPatient);
    els.newOrdonnanceBtn.addEventListener("click", () => toggleOrdonnancePanel(true));
    els.saveOrdonnanceBtn.addEventListener("click", saveOrdonnanceDraft);
    els.closeOrdonnancePanelBtn.addEventListener("click", () => toggleOrdonnancePanel(false));
    els.medicalNotesForm.addEventListener("submit", saveCurrentPatientNotes);
    els.completeVisitBtn.addEventListener("click", completeCurrentVisit);
    els.rescheduleBtn.addEventListener("click", () => openRescheduleModal());
    els.printReportBtn.addEventListener("click", () => printDocument("report"));
    els.printOrdonnanceBtn.addEventListener("click", () => printDocument("ordonnance"));
    els.notificationsBtn.addEventListener("click", showNotificationsSummary);

    els.prevMonth.addEventListener("click", () => changeMonth(-1));
    els.nextMonth.addEventListener("click", () => changeMonth(1));
    els.todayBtn.addEventListener("click", () => {
        state.calendarDate = new Date();
        state.selectedCalendarDate = new Date();
        renderCalendar();
        renderAppointments();
    });

    els.addAppointmentBtn.addEventListener("click", () => toggleAppointmentForm(true));
    els.closeAppointmentFormBtn.addEventListener("click", () => toggleAppointmentForm(false));
    els.cancelAppointmentFormBtn.addEventListener("click", () => toggleAppointmentForm(false));
    els.searchPatientEmailBtn.addEventListener("click", searchPatientByEmail);
    els.appointmentPatientEmail.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            searchPatientByEmail();
        }
    });
    els.appointmentPatient.addEventListener("change", syncSelectedAppointmentPatientPreview);
    els.appointmentForm.addEventListener("submit", createAppointment);

    els.patientSearch.addEventListener("input", filterPatients);
    els.searchPatientBtn.addEventListener("click", filterPatients);
    els.newPatientBtn.addEventListener("click", () => {
        window.location.href = window.doctorDashboardConfig?.registrationUrl || "/inscription";
    });

    els.newConsultationBtn.addEventListener("click", () => {
        switchView("dashboard");
        if (getCurrentAppointment()) {
            els.diagnosis.focus();
        } else {
            showNotification("Aucune consultation active. Creez ou planifiez un rendez-vous.", "error");
        }
    });

    els.exportRecordsBtn.addEventListener("click", exportRecords);
    els.recordsSearchEmail.addEventListener("input", filterRecords);
    els.recordsSearchDossier.addEventListener("input", filterRecords);
    els.clearRecordsSearchBtn.addEventListener("click", resetRecordFilters);
    els.newContactBtn.addEventListener("click", async () => {
        await refreshDashboard();
        showNotification("Les contacts ont ete actualises.");
    });

    els.aboutActionBtn.addEventListener("click", () => {
        window.location.href = window.doctorDashboardConfig?.publicAboutUrl || "/a-propos";
    });

    els.closeRescheduleModalBtn.addEventListener("click", closeRescheduleModal);
    els.cancelRescheduleBtn.addEventListener("click", closeRescheduleModal);
    els.rescheduleForm.addEventListener("submit", submitRescheduleForm);
}

async function bootstrapDashboard() {
    try {
        const user = await ensureDoctorAccess();

        if (!user) {
            return;
        }

        state.user = user;
        await refreshDashboard();
        switchView(state.currentView);
        showNotification("Le dashboard medecin est synchronise avec vos donnees.");
    } catch (error) {
        handleApiError(error);
    }
}

async function ensureDoctorAccess() {
    let user = typeof getAuthData === "function" ? getAuthData().user : null;

    if (!user && typeof getCurrentUser === "function") {
        user = await getCurrentUser();
    }

    if (!user) {
        window.location.href = "/connexion";
        return null;
    }

    if (user.role !== "MEDECIN") {
        window.location.href = typeof resolveDashboardPath === "function"
            ? resolveDashboardPath(user)
            : "/dashboard";
        return null;
    }

    return user;
}

async function refreshDashboard(preserveSelection = true) {
    const response = await apiCall("/doctor/dashboard-data");
    const payload = response.data || response;

    applyDashboardData(payload, preserveSelection);
}

function applyDashboardData(data, preserveSelection = true) {
    state.dashboard = data;

    const patientIds = (data.patients || []).map((item) => item.id);

    state.selectedAppointmentId = data.current_consultation?.appointment_id || null;

    if (!preserveSelection || !patientIds.includes(state.selectedPatientId)) {
        state.selectedPatientId = patientIds[0] || null;
    }

    if (!state.selectedCalendarDate) {
        state.selectedCalendarDate = new Date();
    }

    renderAll();
}

function renderAll() {
    renderDoctorProfile();
    renderCurrentPatient();
    renderQueue();
    renderStats();
    populateAppointmentPatientOptions();
    renderCalendar();
    renderAppointments();
    renderPatientList();
    renderPatientHistory();
    renderConsultations();
    renderRecords();
    renderContacts();
}

function renderDoctorProfile() {
    const doctor = state.dashboard?.doctor;

    if (!doctor) {
        return;
    }

    els.sidebarDoctorAvatar.textContent = doctor.initials || "MD";
    els.sidebarDoctorName.textContent = doctor.full_name || "Medecin";
    els.sidebarDoctorSpecialization.textContent = doctor.specialization || "Medecine generale";
}

function renderCurrentPatient() {
    const appointment = getCurrentAppointment();
    const hasCurrent = Boolean(appointment);

    els.currentConsultationBadge.textContent = hasCurrent ? appointment.status_label : "AUCUN";
    els.currentConsultationBadge.classList.toggle("is-idle", !hasCurrent);
    els.currentPatientAvatar.textContent = appointment?.patient_initials || "NA";
    els.currentPatientName.textContent = appointment?.patient_name || "Aucune consultation active";
    els.currentPatientTurn.textContent = appointment?.turn || "-";
    els.currentPatientAge.textContent = appointment?.age_label || "Age non renseigne";
    els.currentPatientCondition.textContent = appointment?.condition || "Ajoutez un rendez-vous ou attendez le prochain patient.";
    els.currentTurnDisplay.textContent = appointment?.turn || "-";
    els.currentConsultationStatus.textContent = appointment?.status_label || "En attente";
    els.currentConsultationTime.textContent = appointment?.arrival || "--:--";
    els.currentConsultationRoom.textContent = appointment?.room || "--";

    els.diagnosis.value = appointment?.diagnosis || "";
    els.symptoms.value = appointment?.symptoms || "";
    els.treatment.value = appointment?.treatment || "";
    els.nextVisit.value = appointment?.next_visit || "";
    els.ordonnanceDetails.value = appointment?.ordonnance_details || "";
    els.consultationHelperText.textContent = hasCurrent
        ? `Les notes et l'ordonnance sont relies au patient ${appointment.patient_name}.`
        : "Aucune consultation active pour le moment. Le planning reste disponible pour creer un rendez-vous.";

    els.prevPatientBtn.disabled = true;
    els.nextPatientBtn.disabled = true;
    els.nextPatientInlineBtn.disabled = true;

    renderOrdonnanceContext(appointment);
    toggleConsultationControls(!hasCurrent);
}

function toggleConsultationControls(disabled) {
    [
        els.diagnosis,
        els.symptoms,
        els.treatment,
        els.nextVisit,
        els.newOrdonnanceBtn,
        els.saveOrdonnanceBtn,
        els.closeOrdonnancePanelBtn,
        els.ordonnanceDetails,
        els.saveNotesBtn,
        els.completeVisitBtn,
        els.rescheduleBtn,
        els.printReportBtn,
        els.printOrdonnanceBtn,
    ].forEach((element) => {
        element.disabled = disabled;
    });

    if (disabled) {
        toggleOrdonnancePanel(false);
    }
}

function renderOrdonnanceContext(appointment) {
    const hasAppointment = Boolean(appointment);

    els.ordonnancePatientName.textContent = appointment?.patient_name || "Aucun patient selectionne";
    els.ordonnancePatientDossier.textContent = appointment?.patient_display_id || "--";
    els.ordonnancePatientContact.textContent = appointment?.patient_contact || "--";
    els.ordonnancePatientCondition.textContent = appointment?.condition || "--";

    if (!hasAppointment) {
        state.ordonnanceVisible = false;
        els.ordonnancePanel.hidden = true;
    } else {
        els.ordonnancePanel.hidden = !state.ordonnanceVisible;
    }
}

function toggleOrdonnancePanel(visible) {
    if (!getCurrentAppointment()) {
        state.ordonnanceVisible = false;
        els.ordonnancePanel.hidden = true;
        return;
    }

    state.ordonnanceVisible = visible;
    els.ordonnancePanel.hidden = !visible;

    if (visible) {
        els.ordonnanceDetails.focus();
    }
}

function renderQueue() {
    const queue = state.dashboard?.queue || [];
    const waitingCount = state.dashboard?.stats?.waiting_today || 0;

    els.queueCount.textContent = `${waitingCount} waiting`;
    els.patientQueue.innerHTML = queue.length
        ? queue.map((item) => {
            const isActive = item.appointment_id === state.selectedAppointmentId;

            return `
                <div class="queue-item${isActive ? " active" : ""}">
                    <div class="queue-avatar">${item.patient_initials}</div>
                    <div class="queue-info">
                        <h4>${escapeHtml(item.patient_name)}</h4>
                        <p>${escapeHtml(item.turn)} - ${escapeHtml(item.arrival)} - ${escapeHtml(item.specialty)}${isActive ? " - En consultation" : ""}</p>
                    </div>
                </div>
            `;
        }).join("")
        : emptyBlock("Aucun patient en attente pour aujourd'hui.");
}

function renderStats() {
    const stats = state.dashboard?.stats || {};

    els.totalPatients.textContent = String(stats.appointments_today || 0);
    els.completedPatients.textContent = String(stats.completed_today || 0);
    els.waitingPatients.textContent = String(stats.waiting_today || 0);
    els.avgTime.textContent = stats.avg_consultation_delay == null
        ? "--"
        : `${stats.avg_consultation_delay} min`;
    els.progressFill.style.width = `${Math.max(0, Math.min(100, stats.completion_rate || 0))}%`;
}

function renderCalendar() {
    const date = state.calendarDate;
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const appointmentDays = new Set(
        (state.dashboard?.schedule?.all || [])
            .filter((appointment) => {
                const appointmentDate = new Date(appointment.datetime || appointment.date);
                return appointmentDate.getMonth() === month && appointmentDate.getFullYear() === year;
            })
            .map((appointment) => new Date(appointment.datetime || appointment.date).getDate())
    );

    els.currentMonth.textContent = date.toLocaleDateString("fr-FR", {
        month: "long",
        year: "numeric",
    });

    els.calendarDays.innerHTML = "";

    for (let i = 0; i < firstDay.getDay(); i += 1) {
        const placeholder = document.createElement("div");
        placeholder.className = "calendar-day placeholder";
        els.calendarDays.appendChild(placeholder);
    }

    for (let day = 1; day <= lastDay.getDate(); day += 1) {
        const cellDate = new Date(year, month, day);
        const cell = document.createElement("button");
        cell.type = "button";
        cell.className = "calendar-day";
        cell.textContent = String(day);

        if (isSameDay(cellDate, new Date())) {
            cell.classList.add("today");
        }

        if (appointmentDays.has(day)) {
            cell.classList.add("appointment");
        }

        if (isSameDay(cellDate, state.selectedCalendarDate)) {
            cell.classList.add("selected");
        }

        cell.addEventListener("click", () => {
            state.selectedCalendarDate = cellDate;
            renderCalendar();
            renderAppointments();
        });

        els.calendarDays.appendChild(cell);
    }
}

function renderAppointments() {
    const schedule = state.dashboard?.schedule || { all: [], upcoming: [] };
    const selectedDate = state.selectedCalendarDate || new Date();
    const selectedAppointments = schedule.all.filter((appointment) => {
        return isSameDay(new Date(appointment.datetime || appointment.date), selectedDate);
    });

    els.todayAppointmentsTitle.textContent = `Appointments du ${formatShortDate(selectedDate)}`;
    els.todayAppointments.innerHTML = selectedAppointments.length
        ? selectedAppointments.map((appointment) => renderAppointmentCard(appointment, true)).join("")
        : emptyBlock("Aucun rendez-vous sur cette date.");

    els.upcomingAppointments.innerHTML = schedule.upcoming.length
        ? schedule.upcoming.map((appointment) => renderAppointmentCard(appointment, false)).join("")
        : emptyBlock("Aucun rendez-vous cette semaine.");

    document.querySelectorAll("[data-schedule-appointment-id]").forEach((item) => {
        item.addEventListener("click", () => handleScheduleAppointmentClick(Number(item.dataset.scheduleAppointmentId)));
    });
}

function renderAppointmentCard(appointment, primaryList) {
    const label = primaryList ? appointment.status_label : `${appointment.status_label} - ${appointment.date_label}`;

    return `
        <button type="button" class="appointment-item appointment-item--interactive" data-schedule-appointment-id="${appointment.id}">
            <div class="appointment-time">
                <i class="fas fa-clock"></i>
                ${escapeHtml(appointment.time)}
            </div>
            <div class="appointment-patient">${escapeHtml(appointment.patient)}</div>
            <div class="appointment-type">${escapeHtml(appointment.type)} - ${escapeHtml(label)}</div>
        </button>
    `;
}

function handleScheduleAppointmentClick(appointmentId) {
    const queueAppointment = (state.dashboard?.queue || []).find((item) => item.appointment_id === appointmentId);
    const currentAppointment = state.dashboard?.current_consultation;

    if (queueAppointment) {
        if (!currentAppointment || currentAppointment.appointment_id !== appointmentId) {
            showNotification("Seul le patient dont c est le tour peut etre traite. Terminez la consultation en cours pour passer au suivant.", "error");
            return;
        }

        switchView("dashboard");
        return;
    }

    const appointment = (state.dashboard?.schedule?.all || []).find((item) => item.id === appointmentId);

    if (!appointment) {
        return;
    }

    if (appointment.status === "PASSE") {
        showNotification("Ce rendez-vous est deja termine.", "error");
        return;
    }

    state.rescheduleAppointmentId = appointmentId;
    els.rescheduleDate.value = toDateTimeLocalValue(appointment.datetime || appointment.date);
    els.rescheduleReason.value = appointment.type === "Consultation" ? "" : appointment.type;
    openRescheduleModal();
}

function populateAppointmentPatientOptions() {
    const options = state.dashboard?.patient_options || [];

    els.appointmentPatient.innerHTML = options.length
        ? options.map((patient) => `
            <option value="${patient.id}">${escapeHtml(patient.label)} - ${escapeHtml(patient.dossier || `PAT-${patient.id}`)} - ${escapeHtml(patient.email || "email non renseigne")}</option>
        `).join("")
        : '<option value="">Aucun patient disponible</option>';

    if (!els.appointmentDate.value) {
        els.appointmentDate.value = defaultFutureDateTime();
    }

    syncSelectedAppointmentPatientPreview();
}

function getPatientOptions() {
    return state.dashboard?.patient_options || [];
}

function findPatientOptionById(patientId) {
    return getPatientOptions().find((patient) => Number(patient.id) === Number(patientId)) || null;
}

function renderAppointmentPatientPreview(patient, isError = false) {
    if (!patient) {
        els.appointmentPatientResult.textContent = "Saisissez un email pour retrouver un patient deja inscrit, ou utilisez la liste ci-dessous.";
        return;
    }

    if (isError) {
        els.appointmentPatientResult.textContent = patient;
        return;
    }

    els.appointmentPatientResult.innerHTML = `
        <strong>${escapeHtml(patient.label)}</strong><br>
        Dossier: ${escapeHtml(patient.dossier || `PAT-${patient.id}`)}<br>
        Email: ${escapeHtml(patient.email || "--")}<br>
        Telephone: ${escapeHtml(patient.telephone || "--")}
    `;
}

function syncSelectedAppointmentPatientPreview() {
    const selectedPatient = findPatientOptionById(els.appointmentPatient.value);

    if (selectedPatient) {
        state.appointmentLookup = selectedPatient;
        renderAppointmentPatientPreview(selectedPatient);
        return;
    }

    state.appointmentLookup = null;
    renderAppointmentPatientPreview(null);
}

async function searchPatientByEmail() {
    const email = els.appointmentPatientEmail.value.trim().toLowerCase();

    if (!email) {
        renderAppointmentPatientPreview("Veuillez saisir un email patient.", true);
        return;
    }

    try {
        const response = await apiCall(`/doctor/patients/find-by-email?email=${encodeURIComponent(email)}`);
        const patient = response.data || response;

        if (!patient?.id) {
            throw new Error("Aucun patient trouve.");
        }

        state.appointmentLookup = patient;
        els.appointmentPatient.value = String(patient.id);
        renderAppointmentPatientPreview(patient);
        showNotification(`Patient ${patient.label} selectionne pour le rendez-vous.`);
    } catch (error) {
        state.appointmentLookup = null;
        renderAppointmentPatientPreview(error?.message || "Aucun patient correspondant a cet email.", true);
        showNotification(error?.message || "Aucun patient correspondant a cet email.", "error");
    }
}

function renderPatientList() {
    const patients = getVisiblePatients();

    els.patientList.innerHTML = patients.length
        ? patients.map((patient) => `
            <button type="button" class="patient-list-item${patient.id === state.selectedPatientId ? " active" : ""}" data-patient-id="${patient.id}">
                <div class="patient-list-name">${escapeHtml(patient.name)}</div>
                <div class="patient-list-details">
                    <span>${escapeHtml(patient.display_id)}</span>
                    <span>${escapeHtml(patient.condition)}</span>
                </div>
            </button>
        `).join("")
        : emptyBlock("Aucun patient correspondant.");

    els.patientList.querySelectorAll("[data-patient-id]").forEach((item) => {
        item.addEventListener("click", () => {
            state.selectedPatientId = Number(item.dataset.patientId);
            renderPatientList();
            renderPatientHistory();
        });
    });
}

function renderPatientHistory() {
    const patient = getSelectedPatient();

    if (!patient) {
        els.patientHistoryList.innerHTML = emptyBlock("Aucun historique disponible.");
        return;
    }

    els.patientHistoryList.innerHTML = (patient.history || []).length
        ? patient.history.map((entry) => `
            <div class="history-item">
                <div class="history-date">${escapeHtml(entry.date)}</div>
                <div class="history-diagnosis">${escapeHtml(entry.diagnosis)}</div>
                <div class="history-doctor">
                    <i class="fas fa-user-md"></i>
                    <span>${escapeHtml(entry.doctor)}</span>
                </div>
                <div class="history-notes">${escapeHtml(entry.notes)}</div>
            </div>
        `).join("")
        : emptyBlock("Aucune consultation archivee pour ce patient.");
}

function renderConsultations() {
    const consultations = state.dashboard?.consultations || [];

    els.consultationList.innerHTML = consultations.length
        ? consultations.map((consultation) => `
            <article class="consultation-item">
                <h3>${escapeHtml(consultation.patient)}</h3>
                <div class="consultation-meta">
                    <span><strong>Date:</strong> ${escapeHtml(consultation.date_label)}</span>
                    <span><strong>Heure:</strong> ${escapeHtml(consultation.time)}</span>
                    <span><strong>Salle:</strong> ${escapeHtml(consultation.room)}</span>
                </div>
                <span class="consultation-status">${escapeHtml(consultation.status)}</span>
                <p class="consultation-notes">${escapeHtml(consultation.notes)}</p>
            </article>
        `).join("")
        : emptyBlock("Aucune consultation a afficher.");
}

function renderRecords() {
    const records = getVisibleRecords();

    els.recordGrid.innerHTML = records.length
        ? records.map((record) => `
            <article class="record-card">
                <h3>${escapeHtml(record.title)}</h3>
                <div class="record-meta">
                    <span><strong>Patient:</strong> ${escapeHtml(record.patient)}</span>
                    <span><strong>Dossier:</strong> ${escapeHtml(record.dossier_number)}</span>
                    <span><strong>Email:</strong> ${escapeHtml(record.patient_email)}</span>
                    <span><strong>Mise a jour:</strong> ${escapeHtml(record.updated_at)}</span>
                </div>
                <div class="record-details">
                    <div class="record-detail">
                        <span>Diagnostic</span>
                        <strong>${escapeHtml(record.diagnosis)}</strong>
                    </div>
                    <div class="record-detail">
                        <span>Plan de traitement</span>
                        <strong>${escapeHtml(record.treatment)}</strong>
                    </div>
                    <div class="record-detail">
                        <span>Prochaine visite</span>
                        <strong>${escapeHtml(record.next_visit)}</strong>
                    </div>
                </div>
            </article>
        `).join("")
        : emptyBlock("Aucun dossier medical ne correspond a votre recherche.");
}

function renderContacts() {
    const contacts = state.dashboard?.contacts || [];

    els.contactGrid.innerHTML = contacts.length
        ? contacts.map((contact) => `
            <article class="contact-card">
                <h3>${escapeHtml(contact.name)}</h3>
                <div class="contact-meta">
                    <span><strong>Role:</strong> ${escapeHtml(contact.role)}</span>
                    <span><strong>Contact:</strong> ${escapeHtml(contact.details)}</span>
                </div>
                <p>${escapeHtml(contact.extra)}</p>
            </article>
        `).join("")
        : emptyBlock("Aucun contact disponible.");
}

function getVisiblePatients() {
    const patients = state.dashboard?.patients || [];

    if (!state.filteredPatientIds) {
        return patients;
    }

    return patients.filter((patient) => state.filteredPatientIds.includes(patient.id));
}

function getSelectedPatient() {
    return getVisiblePatients().find((patient) => patient.id === state.selectedPatientId)
        || (state.dashboard?.patients || []).find((patient) => patient.id === state.selectedPatientId)
        || null;
}

function getCurrentAppointment() {
    return state.dashboard?.current_consultation || null;
}

function filterPatients() {
    const term = els.patientSearch.value.trim().toLowerCase();
    const patients = state.dashboard?.patients || [];

    if (!term) {
        state.filteredPatientIds = null;
        state.selectedPatientId = patients[0]?.id || null;
        renderPatientList();
        renderPatientHistory();
        return;
    }

    const matched = patients.filter((patient) => {
        return [patient.name, patient.display_id, patient.condition]
            .filter(Boolean)
            .some((field) => field.toLowerCase().includes(term));
    });

    state.filteredPatientIds = matched.map((patient) => patient.id);
    state.selectedPatientId = matched[0]?.id || null;
    renderPatientList();
    renderPatientHistory();
}

function getVisibleRecords() {
    const records = state.dashboard?.records || [];
    const emailFilter = state.recordFilters.email;
    const dossierFilter = state.recordFilters.dossier;

    return records.filter((record) => {
        if (emailFilter && !String(record.patient_email || "").toLowerCase().includes(emailFilter)) {
            return false;
        }

        if (dossierFilter && !String(record.dossier_number || "").toLowerCase().includes(dossierFilter)) {
            return false;
        }

        return true;
    });
}

function filterRecords() {
    state.recordFilters.email = els.recordsSearchEmail.value.trim().toLowerCase();
    state.recordFilters.dossier = els.recordsSearchDossier.value.trim().toLowerCase();
    renderRecords();
}

function resetRecordFilters() {
    state.recordFilters.email = "";
    state.recordFilters.dossier = "";
    els.recordsSearchEmail.value = "";
    els.recordsSearchDossier.value = "";
    renderRecords();
}

async function persistCurrentConsultation(successMessage) {
    const appointment = getCurrentAppointment();

    if (!appointment) {
        showNotification("Aucune consultation active a enregistrer.", "error");
        return false;
    }

    const response = await apiCall(`/doctor/appointments/${appointment.appointment_id}/notes`, "PUT", getConsultationPayload());
    applyDashboardData(response.data || response);
    showNotification(successMessage);

    return true;
}

async function saveCurrentPatientNotes(event) {
    event.preventDefault();

    try {
        await persistCurrentConsultation("Les notes ont ete enregistrees avec succes.");
    } catch (error) {
        handleApiError(error);
    }
}

async function saveOrdonnanceDraft() {
    if (!els.ordonnanceDetails.value.trim()) {
        showNotification("Veuillez saisir le contenu de l'ordonnance avant la sauvegarde.", "error");
        return;
    }

    try {
        const saved = await persistCurrentConsultation("L'ordonnance a ete sauvegardee avec succes.");

        if (saved) {
            toggleOrdonnancePanel(true);
        }
    } catch (error) {
        handleApiError(error);
    }
}

async function completeCurrentVisit() {
    const appointment = getCurrentAppointment();

    if (!appointment) {
        showNotification("Aucune consultation active a cloturer.", "error");
        return;
    }

    try {
        const response = await apiCall(`/doctor/appointments/${appointment.appointment_id}/complete`, "POST", getConsultationPayload());
        applyDashboardData(response.data || response, false);
        showNotification("La consultation a ete cloturee.");
    } catch (error) {
        handleApiError(error);
    }
}

async function createAppointment(event) {
    event.preventDefault();

    const selectedPatientId = Number(state.appointmentLookup?.id || els.appointmentPatient.value);
    const appointmentDate = els.appointmentDate.value;

    if (!selectedPatientId) {
        showNotification("Veuillez selectionner un patient avant de creer le rendez-vous.", "error");
        return;
    }

    if (!appointmentDate) {
        showNotification("Veuillez choisir une date et une heure pour le rendez-vous.", "error");
        return;
    }

    if (new Date(appointmentDate) <= new Date()) {
        showNotification("Le rendez-vous doit etre programme dans le futur.", "error");
        return;
    }

    try {
        const response = await apiCall("/doctor/appointments", "POST", {
            patient_id: selectedPatientId,
            appointment_date: appointmentDate,
            reason: els.appointmentReason.value,
        });

        applyDashboardData(response.data || response, false);
        els.appointmentForm.reset();
        els.appointmentDate.value = defaultFutureDateTime();
        state.appointmentLookup = null;
        renderAppointmentPatientPreview(null);
        toggleAppointmentForm(false);
        state.selectedCalendarDate = new Date();
        state.calendarDate = new Date();
        showNotification("Le rendez-vous a ete ajoute au planning.");
    } catch (error) {
        handleApiError(error);
    }
}

function openRescheduleModal() {
    const appointment = getCurrentAppointment();

    if (!appointment && !state.rescheduleAppointmentId) {
        showNotification("Aucun rendez-vous a reprogrammer.", "error");
        return;
    }

    const appointmentId = state.rescheduleAppointmentId || appointment.appointment_id;
    const scheduleAppointment = (state.dashboard?.schedule?.all || []).find((item) => item.id === appointmentId);

    state.rescheduleAppointmentId = appointmentId;
    els.rescheduleDate.value = scheduleAppointment?.datetime
        ? toDateTimeLocalValue(scheduleAppointment.datetime)
        : defaultFutureDateTime();
    els.rescheduleReason.value = scheduleAppointment?.type === "Consultation" ? "" : (scheduleAppointment?.type || appointment?.condition || "");
    els.rescheduleModal.hidden = false;
}

function closeRescheduleModal() {
    els.rescheduleModal.hidden = true;
    state.rescheduleAppointmentId = null;
}

async function submitRescheduleForm(event) {
    event.preventDefault();

    const appointmentId = state.rescheduleAppointmentId || getCurrentAppointment()?.appointment_id;

    if (!appointmentId) {
        showNotification("Aucun rendez-vous selectionne.", "error");
        return;
    }

    try {
        const response = await apiCall(`/doctor/appointments/${appointmentId}/reschedule`, "PATCH", {
            appointment_date: els.rescheduleDate.value,
            reason: els.rescheduleReason.value,
        });

        applyDashboardData(response.data || response, false);
        closeRescheduleModal();
        showNotification("Le rendez-vous a ete reprogramme.");
    } catch (error) {
        handleApiError(error);
    }
}

function toggleAppointmentForm(visible) {
    els.appointmentFormPanel.hidden = !visible;

    if (visible && !els.appointmentDate.value) {
        els.appointmentDate.value = defaultFutureDateTime();
    }

    if (visible) {
        syncSelectedAppointmentPatientPreview();
        return;
    }

    state.appointmentLookup = null;
    els.appointmentPatientEmail.value = "";
    renderAppointmentPatientPreview(null);
}

function showNotificationsSummary() {
    const notifications = state.dashboard?.notifications;

    if (!notifications) {
        return;
    }

    if (!notifications.total) {
        showNotification("Aucune notification pour le moment.");
        return;
    }

    showNotification(`${notifications.unread} notification(s) non lue(s) sur ${notifications.total}.`);
}

function goToNextPatient() {
    showNotification("Le passage au patient suivant se fait uniquement apres avoir cloture la consultation en cours.", "error");
}

function goToPreviousPatient() {
    showNotification("La consultation suit strictement l ordre de la file d attente.", "error");
}

function changeMonth(offset) {
    state.calendarDate = new Date(
        state.calendarDate.getFullYear(),
        state.calendarDate.getMonth() + offset,
        1
    );
    renderCalendar();
}

function printDocument(type) {
    const appointment = getCurrentAppointment();

    if (!appointment) {
        showNotification("Aucune consultation active a imprimer.", "error");
        return;
    }

    const isOrdonnance = type === "ordonnance";
    const title = isOrdonnance ? "Ordonnance" : "Rapport de consultation";
    const doctor = state.dashboard?.doctor || {};
    const patientContact = appointment.patient_contact || "--";
    const ordonnanceDetails = els.ordonnanceDetails.value.trim() || appointment.ordonnance_details || "Aucune ordonnance disponible";
    const patientAge = appointment.age_label || "Age non renseigne";
    const patientGender = appointment.patient_gender || "Non renseigne";
    const consultationDate = formatShortDate(new Date());
    const bodyContent = isOrdonnance
        ? `
            <section class="ordonnance-sheet">
                <div class="ordonnance-watermark">Rx</div>
                <header class="ordonnance-header">
                    <div>
                        <h1>${escapeHtml(doctor.full_name || "Medecin")}</h1>
                        <p>${escapeHtml(doctor.specialization || "Consultation generale")}</p>
                    </div>
                    <div class="ordonnance-brand">
                        <strong>Medicare</strong>
                        <span>Cabinet medical coordonne</span>
                    </div>
                </header>

                <section class="ordonnance-meta">
                    <div class="ordonnance-line"><span>Patient</span><strong>${escapeHtml(appointment.patient_name)}</strong></div>
                    <div class="ordonnance-line"><span>Age</span><strong>${escapeHtml(patientAge)}</strong></div>
                    <div class="ordonnance-line"><span>Date</span><strong>${escapeHtml(consultationDate)}</strong></div>
                    <div class="ordonnance-line"><span>Sexe</span><strong>${escapeHtml(patientGender)}</strong></div>
                    <div class="ordonnance-line ordonnance-line--full"><span>Dossier</span><strong>${escapeHtml(appointment.patient_display_id || "--")}</strong></div>
                    <div class="ordonnance-line ordonnance-line--full"><span>Contact</span><strong>${escapeHtml(patientContact)}</strong></div>
                </section>

                <section class="ordonnance-rx">
                    <div class="ordonnance-rx__title">Rx</div>
                    <div class="ordonnance-rx__body">${escapeHtml(ordonnanceDetails).replace(/\n/g, "<br>")}</div>
                </section>

                <footer class="ordonnance-footer">
                    <div>
                        <strong>Contact cabinet</strong>
                        <span>${escapeHtml(doctor.telephone || doctor.email || "Contact non renseigne")}</span>
                    </div>
                    <div class="ordonnance-signature">
                        <span>Signature et cachet</span>
                    </div>
                </footer>
            </section>
        `
        : `
            <section class="print-section">
                <div class="print-grid">
                    <div><span>Patient</span><strong>${escapeHtml(appointment.patient_name)}</strong></div>
                    <div><span>Tour</span><strong>${escapeHtml(appointment.turn)}</strong></div>
                    <div><span>Cabinet</span><strong>${escapeHtml(appointment.room || "--")}</strong></div>
                    <div><span>Date</span><strong>${escapeHtml(formatShortDate(new Date()))}</strong></div>
                </div>
            </section>
            <section class="print-section">
                <h2>Compte rendu medical</h2>
                <p><strong>Diagnostic :</strong> ${escapeHtml(appointment.diagnosis || "Non renseigne")}</p>
                <p><strong>Symptomes :</strong> ${escapeHtml(appointment.symptoms || "Non renseignes")}</p>
                <p><strong>Traitement :</strong> ${escapeHtml(appointment.treatment || "Non renseigne")}</p>
                <p><strong>Suivi :</strong> ${escapeHtml(appointment.next_visit || "Aucun")}</p>
            </section>
        `;

    const printWindow = window.open("", "_blank", "width=900,height=700");

    if (!printWindow) {
        showNotification("La fenetre d'impression a ete bloquee par le navigateur.", "error");
        return;
    }

    printWindow.document.write(`
        <html lang="fr">
            <head>
                <title>${title}</title>
                <style>
                    * { box-sizing: border-box; }
                    body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #0f172a; background: #ffffff; }
                    .page { padding: 42px 48px 30px; }
                    .print-header { border-bottom: 3px solid #38bdf8; padding-bottom: 18px; margin-bottom: 28px; }
                    .print-header h1 { margin: 0 0 8px; color: #0284c7; font-size: 28px; }
                    .print-header p { margin: 0; color: #475569; line-height: 1.6; }
                    .print-section { margin-bottom: 22px; }
                    .print-section h2 { margin: 0 0 14px; color: #0ea5e9; font-size: 18px; }
                    .print-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
                    .print-grid div { padding: 14px; border: 1px solid #d8e7f5; border-radius: 12px; background: #f7fbff; }
                    .print-grid span { display: block; margin-bottom: 4px; color: #64748b; font-size: 12px; text-transform: uppercase; }
                    .print-grid strong { font-size: 15px; }
                    .prescription-body { min-height: 220px; padding: 18px; border: 1px solid #d8e7f5; border-radius: 12px; background: #f7fbff; line-height: 1.75; }
                    p { line-height: 1.6; margin: 0 0 12px; }
                    .print-footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #d8e7f5; display: flex; justify-content: space-between; gap: 16px; color: #64748b; font-size: 12px; }
                    .ordonnance-sheet { position: relative; min-height: 980px; padding: 38px 42px 28px; border: 1px solid #d8e7f5; border-radius: 24px; overflow: hidden; background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); }
                    .ordonnance-watermark { position: absolute; inset: 50% auto auto 50%; transform: translate(-50%, -50%); font-size: 180px; font-weight: 700; color: rgba(14, 165, 233, 0.06); letter-spacing: 6px; }
                    .ordonnance-header { position: relative; display: flex; justify-content: space-between; align-items: start; gap: 20px; padding-bottom: 20px; margin-bottom: 24px; border-bottom: 2px solid #cfe6f7; }
                    .ordonnance-header h1 { margin: 0 0 6px; font-size: 30px; color: #0f172a; }
                    .ordonnance-header p { margin: 0; color: #475569; }
                    .ordonnance-brand { text-align: right; color: #0369a1; }
                    .ordonnance-brand strong { display: block; font-size: 24px; }
                    .ordonnance-brand span { font-size: 13px; color: #64748b; }
                    .ordonnance-meta { position: relative; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 18px; margin-bottom: 28px; }
                    .ordonnance-line { padding-bottom: 8px; border-bottom: 1px solid #dbeafe; }
                    .ordonnance-line span { display: inline-block; min-width: 90px; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
                    .ordonnance-line strong { font-size: 15px; }
                    .ordonnance-line--full { grid-column: 1 / -1; }
                    .ordonnance-rx { position: relative; min-height: 460px; margin-bottom: 36px; padding: 24px 24px 30px 84px; border-radius: 24px; border: 1px solid #d8e7f5; background: rgba(255, 255, 255, 0.92); }
                    .ordonnance-rx__title { position: absolute; left: 26px; top: 18px; font-size: 42px; font-weight: 700; color: #0284c7; }
                    .ordonnance-rx__body { position: relative; z-index: 1; min-height: 380px; line-height: 1.85; font-size: 16px; white-space: normal; }
                    .ordonnance-footer { display: flex; justify-content: space-between; align-items: end; gap: 20px; padding-top: 18px; border-top: 2px solid #d8e7f5; color: #475569; font-size: 13px; }
                    .ordonnance-footer strong { display: block; margin-bottom: 4px; color: #0f172a; }
                    .ordonnance-signature { min-width: 220px; padding-top: 40px; border-top: 1px solid #94a3b8; text-align: center; }
                </style>
            </head>
            <body>
                <div class="page">
                    <header class="print-header">
                        <h1>${title}</h1>
                        <p>Cabinet Medicare<br>${escapeHtml(doctor.full_name || "Medecin")} - ${escapeHtml(doctor.specialization || "Consultation generale")}</p>
                    </header>
                    ${bodyContent}
                    <footer class="print-footer">
                        <span>Document prepare pour impression PDF</span>
                        <span>${escapeHtml(new Date().toLocaleDateString("fr-FR"))}</span>
                    </footer>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

function exportRecords() {
    const records = getVisibleRecords();

    if (!records.length) {
        showNotification("Aucun dossier a exporter.", "error");
        return;
    }

    const blob = new Blob([JSON.stringify(records, null, 2)], {
        type: "application/json",
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `doctor-records-${new Date().toISOString().slice(0, 10)}.json`;
    link.click();
    URL.revokeObjectURL(url);

    showNotification("Export JSON genere avec succes.");
}

function getConsultationPayload() {
    return {
        diagnosis: els.diagnosis.value,
        symptoms: els.symptoms.value,
        treatment: els.treatment.value,
        next_visit: els.nextVisit.value || null,
        ordonnance_details: els.ordonnanceDetails.value,
    };
}

function switchView(viewName) {
    state.currentView = viewName;
    els.navItems.forEach((item) => item.classList.toggle("active", item.dataset.view === viewName));
    els.viewContainers.forEach((container) => container.classList.toggle("active", container.id === `${viewName}-view`));
    els.viewTitle.textContent = getViewLabel(viewName);
    closeSidebar();
}

function getViewLabel(viewName) {
    return {
        dashboard: "Dashboard",
        schedule: "Schedule",
        patients: "Patients",
        consultations: "Consultations",
        records: "Dossiers medicaux",
        contacts: "Contacts",
        about: "About",
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
    }, 2800);
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

function formatShortDate(date) {
    return date.toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function isSameDay(first, second) {
    return first.getDate() === second.getDate()
        && first.getMonth() === second.getMonth()
        && first.getFullYear() === second.getFullYear();
}

function toDateTimeLocalValue(value) {
    const date = value instanceof Date ? value : new Date(value);
    const pad = (number) => String(number).padStart(2, "0");

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function defaultFutureDateTime() {
    return toDateTimeLocalValue(nextBookableDateTime(new Date()));
}

function nextBookableDateTime(baseDate) {
    const date = new Date(baseDate);
    const allowedHours = [8, 9, 10, 11, 12, 14, 15];

    date.setMinutes(0, 0, 0);

    if (baseDate.getMinutes() > 0 || baseDate.getSeconds() > 0 || baseDate.getMilliseconds() > 0) {
        date.setHours(date.getHours() + 1);
    }

    while (true) {
        if (date.getDay() === 0 || date.getDay() === 6) {
            date.setDate(date.getDate() + 1);
            date.setHours(8, 0, 0, 0);
            continue;
        }

        const nextHour = allowedHours.find((hour) => hour >= date.getHours());

        if (typeof nextHour === "number") {
            date.setHours(nextHour, 0, 0, 0);

            if (date > new Date()) {
                return date;
            }

            date.setHours(date.getHours() + 1, 0, 0, 0);
            continue;
        }

        date.setDate(date.getDate() + 1);
        date.setHours(8, 0, 0, 0);
    }
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

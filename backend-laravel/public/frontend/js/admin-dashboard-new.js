const adminDashboardState = {
    user: null,
    data: null,
    activeSection: "dashboard",
    patientFilter: "all",
    globalSearch: "",
    search: {
        patientId: "",
    },
    selectedPatientId: null,
    editing: {
        patientId: null,
        doctorId: null,
        secretaryId: null,
    },
};

document.addEventListener("DOMContentLoaded", () => {
    syncTheme();
    initAdminDashboard().catch((error) => {
        console.error("Admin dashboard init error:", error);
        showNotice(error.message || "Impossible de charger le dashboard.", "error");
    });
});

function syncTheme() {
    const theme = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", theme);
    document.body?.setAttribute("data-theme", theme);
}

async function initAdminDashboard() {
    let user = getAuthData().user;

    if (!user) {
        user = await getCurrentUser();
    }

    if (!user) {
        window.location.replace("/connexion");
        return;
    }

    if (user.role !== "ADMIN") {
        window.location.replace(resolveDashboardPath(user));
        return;
    }

    adminDashboardState.user = user;

    bindSidebarNavigation();
    bindGlobalEvents();
    renderCurrentUser();
    await loadDashboardData();
}

async function loadDashboardData() {
    const response = await apiCall("/admin/dashboard-data");
    adminDashboardState.data = response.data || response;
    renderDashboard();
}

function bindSidebarNavigation() {
    document.querySelectorAll(".menu-item[data-section]").forEach((button) => {
        button.addEventListener("click", () => showSection(button.dataset.section));
    });
}

function bindGlobalEvents() {
    document.getElementById("logoutButton")?.addEventListener("click", logout);

    document.getElementById("globalSearch")?.addEventListener("input", (event) => {
        adminDashboardState.globalSearch = event.target.value.trim().toLowerCase();
        renderSectionTables();
    });

    document.getElementById("searchButton")?.addEventListener("click", () => {
        adminDashboardState.search.patientId = document.getElementById("patientId").value.trim().toLowerCase();
        renderPatientsTables();
    });

    document.getElementById("resetSearchButton")?.addEventListener("click", resetPatientSearch);
    document.getElementById("closeDetails")?.addEventListener("click", hidePatientDetails);
    document.getElementById("closePatientBottomButton")?.addEventListener("click", hidePatientDetails);
    document.getElementById("updateRecordButton")?.addEventListener("click", () => {
        const patient = getSelectedPatient();
        if (patient) {
            openPatientForm(patient);
        }
    });
    document.getElementById("resetPatientPasswordButton")?.addEventListener("click", () => {
        const patient = getSelectedPatient();
        if (patient) {
            resetPassword("patient", patient.id, patient.name);
        }
    });
    document.getElementById("deletePatientButton")?.addEventListener("click", () => {
        const patient = getSelectedPatient();
        if (patient) {
            deleteEntity("patient", patient.id, patient.name);
        }
    });

    document.querySelectorAll("#patientTabs .tab").forEach((tab) => {
        tab.addEventListener("click", () => {
            adminDashboardState.patientFilter = tab.dataset.filter;
            document.querySelectorAll("#patientTabs .tab").forEach((item) => item.classList.remove("active"));
            tab.classList.add("active");
            renderPatientsTables();
        });
    });

    document.getElementById("openDoctorFormBtn")?.addEventListener("click", () => openDoctorForm());
    document.getElementById("openSecretaryFormBtn")?.addEventListener("click", () => openSecretaryForm());
    document.getElementById("closeDoctorFormBtn")?.addEventListener("click", closeDoctorForm);
    document.getElementById("closeSecretaryFormBtn")?.addEventListener("click", closeSecretaryForm);
    document.getElementById("closePatientFormBtn")?.addEventListener("click", closePatientForm);
    document.getElementById("doctorCreateForm")?.addEventListener("submit", handleDoctorSubmit);
    document.getElementById("secretaryCreateForm")?.addEventListener("submit", handleSecretarySubmit);
    document.getElementById("patientUpdateForm")?.addEventListener("submit", handlePatientSubmit);

    document.addEventListener("click", (event) => {
        const viewPatientButton = event.target.closest('[data-action="view-patient"]');
        if (viewPatientButton) {
            showPatientDetails(Number(viewPatientButton.dataset.patientId));
            return;
        }

        const editPatientButton = event.target.closest('[data-action="edit-patient"]');
        if (editPatientButton) {
            const patient = findPatientById(Number(editPatientButton.dataset.patientId));
            if (patient) {
                openPatientForm(patient);
            }
            return;
        }

        const deletePatientButton = event.target.closest('[data-action="delete-patient"]');
        if (deletePatientButton) {
            const patient = findPatientById(Number(deletePatientButton.dataset.patientId));
            if (patient) {
                deleteEntity("patient", patient.id, patient.name);
            }
            return;
        }

        const resetPatientButton = event.target.closest('[data-action="reset-patient-password"]');
        if (resetPatientButton) {
            const patient = findPatientById(Number(resetPatientButton.dataset.patientId));
            if (patient) {
                resetPassword("patient", patient.id, patient.name);
            }
            return;
        }

        const editDoctorButton = event.target.closest('[data-action="edit-doctor"]');
        if (editDoctorButton) {
            const doctor = findDoctorById(Number(editDoctorButton.dataset.doctorId));
            if (doctor) {
                openDoctorForm(doctor);
            }
            return;
        }

        const deleteDoctorButton = event.target.closest('[data-action="delete-doctor"]');
        if (deleteDoctorButton) {
            const doctor = findDoctorById(Number(deleteDoctorButton.dataset.doctorId));
            if (doctor) {
                deleteEntity("doctor", doctor.id, doctor.full_name);
            }
            return;
        }

        const resetDoctorButton = event.target.closest('[data-action="reset-doctor-password"]');
        if (resetDoctorButton) {
            const doctor = findDoctorById(Number(resetDoctorButton.dataset.doctorId));
            if (doctor) {
                resetPassword("doctor", doctor.id, doctor.full_name);
            }
            return;
        }

        const editSecretaryButton = event.target.closest('[data-action="edit-secretary"]');
        if (editSecretaryButton) {
            const secretary = findSecretaryById(Number(editSecretaryButton.dataset.secretaryId));
            if (secretary) {
                openSecretaryForm(secretary);
            }
            return;
        }

        const deleteSecretaryButton = event.target.closest('[data-action="delete-secretary"]');
        if (deleteSecretaryButton) {
            const secretary = findSecretaryById(Number(deleteSecretaryButton.dataset.secretaryId));
            if (secretary) {
                deleteEntity("secretary", secretary.id, secretary.full_name);
            }
            return;
        }

        const resetSecretaryButton = event.target.closest('[data-action="reset-secretary-password"]');
        if (resetSecretaryButton) {
            const secretary = findSecretaryById(Number(resetSecretaryButton.dataset.secretaryId));
            if (secretary) {
                resetPassword("secretary", secretary.id, secretary.full_name);
            }
        }
    });
}

function renderCurrentUser() {
    const user = adminDashboardState.user;
    const fullName = [user.prenom, user.nom].filter(Boolean).join(" ") || user.email || "Administrateur";
    const initials = `${(user.prenom || "A").charAt(0)}${(user.nom || "D").charAt(0)}`.toUpperCase();

    document.getElementById("sidebarUserName").textContent = fullName;
    document.getElementById("sidebarUserRole").textContent = "Administrateur";
    document.getElementById("sidebarUserAvatar").textContent = initials;
    renderHeaderContext();
}

function renderDashboard() {
    renderSummaryCards();
    renderAdvancedStats();
    renderSearchFilters();
    renderDoctorsTable();
    renderSecretariesTable();
    renderPatientsTables();
    showSection(adminDashboardState.activeSection);
}

function renderSummaryCards() {
    const summary = adminDashboardState.data.summary;

    document.getElementById("statTotalPatients").textContent = formatNumber(summary.total_patients);
    document.getElementById("statTotalDoctors").textContent = formatNumber(summary.total_doctors);
    document.getElementById("statTodayAppointments").textContent = formatNumber(summary.today_appointments);
    document.getElementById("statTotalConsultations").textContent = formatNumber(summary.consultations_total);
    document.getElementById("statCancellationRate").textContent = `${summary.cancellation_rate}%`;
    document.getElementById("statCompletionRate").textContent = `${summary.completion_rate}%`;
}

function renderAdvancedStats() {
    const advanced = adminDashboardState.data.advanced;

    renderSimpleTable(
        "appointmentsByDoctorBody",
        advanced.appointments_by_doctor,
        (row) => `
            <tr>
                <td data-label="Medecin">${escapeHtml(row.name)}</td>
                <td data-label="Rendez-vous">${formatNumber(row.count)}</td>
            </tr>
        `,
        2,
        "Aucun rendez-vous par medecin."
    );

    renderSimpleTable(
        "consultationsByDoctorBody",
        advanced.consultations_by_doctor,
        (row) => `
            <tr>
                <td data-label="Medecin">${escapeHtml(row.name)}</td>
                <td data-label="Consultations">${formatNumber(row.count)}</td>
            </tr>
        `,
        2,
        "Aucune consultation par medecin."
    );

    renderSimpleTable(
        "patientsFollowedByDoctorBody",
        advanced.patients_followed_by_doctor,
        (row) => `
            <tr>
                <td data-label="Medecin">${escapeHtml(row.name)}</td>
                <td data-label="Patients">${formatNumber(row.count)}</td>
            </tr>
        `,
        2,
        "Aucun patient unique suivi."
    );

    const mostActive = advanced.most_active_doctor;
    document.getElementById("mostActiveDoctorName").textContent = mostActive ? mostActive.name : "Aucun medecin";
    document.getElementById("mostActiveDoctorMeta").textContent = mostActive
        ? `${formatNumber(mostActive.consultations)} consultations et ${formatNumber(mostActive.appointments)} rendez-vous`
        : "Aucune activite exploitable";
    document.getElementById("cancelledAppointmentsCount").textContent = formatNumber(advanced.cancelled_appointments);
    document.getElementById("completedAppointmentsCount").textContent = formatNumber(advanced.completed_appointments);

    renderConsultationFlow(advanced.consultation_flow || []);
}

function renderConsultationFlow(flow) {
    const container = document.getElementById("consultationFlowBars");
    const maxCount = Math.max(1, ...flow.map((item) => item.count));

    if (!flow.length) {
        container.innerHTML = '<p class="section-subtitle">Aucun flux de consultation disponible.</p>';
        return;
    }

    container.innerHTML = flow.map((item) => {
        const width = Math.max(4, Math.round((item.count / maxCount) * 100));

        return `
            <div class="progress-row">
                <div class="progress-label">${escapeHtml(item.date)}</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:${width}%"></div>
                </div>
                <div class="progress-value">${formatNumber(item.count)}</div>
            </div>
        `;
    }).join("");
}

function renderSearchFilters() {
    document.getElementById("patientId").value = adminDashboardState.search.patientId;
}

function renderPatientsTables() {
    const patients = getFilteredPatients();

    renderSimpleTable(
        "dashboardPatientsTableBody",
        patients,
        (patient) => patientRow(patient, true),
        6,
        "Aucun patient ne correspond aux filtres."
    );

    renderSimpleTable(
        "patientsRegistryTableBody",
        patients,
        (patient) => patientRow(patient, false),
        6,
        "Aucun patient disponible."
    );

    if (adminDashboardState.selectedPatientId) {
        const selectedPatient = patients.find((patient) => patient.id === adminDashboardState.selectedPatientId)
            || findPatientById(adminDashboardState.selectedPatientId);
        if (selectedPatient) {
            renderPatientDetails(selectedPatient);
        } else {
            hidePatientDetails();
        }
    }
}

function patientRow(patient, dashboardRow) {
    const actions = `
        <button class="action-btn btn-view" type="button" data-action="view-patient" data-patient-id="${patient.id}">
            <i class="fas fa-eye"></i> View
        </button>
        <button class="action-btn btn-edit" type="button" data-action="edit-patient" data-patient-id="${patient.id}">
            <i class="fas fa-pen"></i> Update
        </button>
        <button class="action-btn btn-edit" type="button" data-action="reset-patient-password" data-patient-id="${patient.id}">
            <i class="fas fa-key"></i> Reset
        </button>
        <button class="action-btn btn-danger" type="button" data-action="delete-patient" data-patient-id="${patient.id}">
            <i class="fas fa-trash"></i> Delete
        </button>
    `;

    return `
        <tr>
            <td data-label="ID">${escapeHtml(patient.patient_id)}</td>
            <td data-label="Nom">${escapeHtml(patient.name)}</td>
            <td data-label="Hopital">${escapeHtml(patient.hospital)}</td>
            <td data-label="Admission">${escapeHtml(patient.admission_date)}</td>
            <td data-label="Statut"><span class="status ${escapeAttribute(patient.status_class)}">${escapeHtml(patient.status_label)}</span></td>
            <td class="action-buttons" data-label="Actions">${actions}</td>
        </tr>
    `;
}

function renderDoctorsTable() {
    const doctors = filterCollectionBySearch(adminDashboardState.data.doctors || []);
    const cabinetSelect = document.getElementById("doctorCabinet");

    renderSimpleTable(
        "doctorsTableBody",
        doctors,
        (doctor) => `
            <tr>
                <td data-label="ID">#${doctor.id}</td>
                <td data-label="Nom">${escapeHtml(doctor.full_name)}</td>
                <td data-label="Login">${escapeHtml(doctor.login || "--")}</td>
                <td data-label="Email">${escapeHtml(doctor.email)}</td>
                <td data-label="Telephone">${escapeHtml(doctor.telephone || "--")}</td>
                <td data-label="Specialite">${escapeHtml(doctor.specialization || "--")}</td>
                <td data-label="Licence">${escapeHtml(doctor.license_number || "--")}</td>
                <td data-label="Cabinet">${escapeHtml(doctor.cabinet || "Sans cabinet")}</td>
                <td data-label="RDV">${formatNumber(doctor.appointments_count)}</td>
                <td data-label="Consultations">${formatNumber(doctor.consultations_count)}</td>
                <td class="action-buttons" data-label="Actions">
                    <button class="action-btn btn-edit" type="button" data-action="edit-doctor" data-doctor-id="${doctor.id}">
                        <i class="fas fa-pen"></i> Update
                    </button>
                    <button class="action-btn btn-edit" type="button" data-action="reset-doctor-password" data-doctor-id="${doctor.id}">
                        <i class="fas fa-key"></i> Reset
                    </button>
                    <button class="action-btn btn-danger" type="button" data-action="delete-doctor" data-doctor-id="${doctor.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `,
        11,
        "Aucun medecin disponible."
    );

    cabinetSelect.innerHTML = '<option value="">Sans cabinet</option>' + (adminDashboardState.data.cabinets || []).map((cabinet) => {
        return `<option value="${cabinet.id}">${escapeHtml(cabinet.nom)}</option>`;
    }).join("");
}

function renderSecretariesTable() {
    const secretaries = filterCollectionBySearch(adminDashboardState.data.secretaries || []);

    renderSimpleTable(
        "secretariesTableBody",
        secretaries,
        (secretary) => `
            <tr>
                <td data-label="ID">#${secretary.id}</td>
                <td data-label="Nom">${escapeHtml(secretary.full_name)}</td>
                <td data-label="Login">${escapeHtml(secretary.login || "--")}</td>
                <td data-label="Email">${escapeHtml(secretary.email)}</td>
                <td data-label="Telephone">${escapeHtml(secretary.telephone || "--")}</td>
                <td data-label="Bureau">${escapeHtml(secretary.office_number || "--")}</td>
                <td data-label="Affectation">${escapeHtml(secretary.assignment || "--")}</td>
                <td class="action-buttons" data-label="Actions">
                    <button class="action-btn btn-edit" type="button" data-action="edit-secretary" data-secretary-id="${secretary.id}">
                        <i class="fas fa-pen"></i> Update
                    </button>
                    <button class="action-btn btn-edit" type="button" data-action="reset-secretary-password" data-secretary-id="${secretary.id}">
                        <i class="fas fa-key"></i> Reset
                    </button>
                    <button class="action-btn btn-danger" type="button" data-action="delete-secretary" data-secretary-id="${secretary.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `,
        8,
        "Aucune secretaire disponible."
    );
}

function showSection(sectionName) {
    adminDashboardState.activeSection = sectionName;

    document.querySelectorAll(".menu-item[data-section]").forEach((button) => {
        button.classList.toggle("active", button.dataset.section === sectionName);
    });

    document.querySelectorAll(".content-section").forEach((section) => {
        section.classList.toggle("active", section.dataset.section === sectionName);
    });

    renderHeaderContext();
    renderSectionTables();
}

function renderHeaderContext() {
    const titles = {
        dashboard: "Dashboard Admin",
        patients: "Patients",
        doctors: "Doctors",
        secretaries: "Secretaire",
    };

    const currentDate = new Date().toLocaleDateString("fr-FR", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });

    document.getElementById("pageContextTitle").textContent = titles[adminDashboardState.activeSection] || "Dashboard Admin";
    document.getElementById("pageContextDate").textContent = currentDate;
}

function renderSectionTables() {
    if (adminDashboardState.activeSection === "dashboard" || adminDashboardState.activeSection === "patients") {
        renderPatientsTables();
        return;
    }

    if (adminDashboardState.activeSection === "doctors") {
        renderDoctorsTable();
        return;
    }

    renderSecretariesTable();
}

function getFilteredPatients() {
    const patients = adminDashboardState.data?.patients || [];

    return patients.filter((patient) => {
        if (adminDashboardState.patientFilter === "outpatients" && patient.patient_type !== "outpatients") {
            return false;
        }

        if (adminDashboardState.patientFilter === "inpatients" && patient.patient_type !== "inpatients") {
            return false;
        }

        if (adminDashboardState.patientFilter === "appointments" && !patient.has_appointments) {
            return false;
        }

        if (adminDashboardState.search.patientId) {
            const haystack = `${patient.patient_id} ${patient.name}`.toLowerCase();
            if (!haystack.includes(adminDashboardState.search.patientId)) {
                return false;
            }
        }

        if (adminDashboardState.globalSearch) {
            const haystack = `${patient.patient_id} ${patient.name} ${patient.hospital} ${patient.status_label} ${patient.email || ""}`.toLowerCase();
            if (!haystack.includes(adminDashboardState.globalSearch)) {
                return false;
            }
        }

        return true;
    });
}

function filterCollectionBySearch(collection) {
    if (!adminDashboardState.globalSearch) {
        return collection;
    }

    return collection.filter((item) => Object.values(item).join(" ").toLowerCase().includes(adminDashboardState.globalSearch));
}

function showPatientDetails(patientId) {
    const patient = findPatientById(patientId);

    if (!patient) {
        showNotice("Patient introuvable dans les donnees chargees.", "error");
        return;
    }

    adminDashboardState.selectedPatientId = patientId;
    renderPatientDetails(patient);
    document.getElementById("patientDetails").scrollIntoView({ behavior: "smooth", block: "start" });
}

function renderPatientDetails(patient) {
    const details = patient.details;
    const detailElement = document.getElementById("patientDetails");

    document.getElementById("patientDetailName").textContent = details.title;
    document.getElementById("patientDetailId").textContent = `Patient ID: ${details.patient_id}`;
    document.getElementById("patientDetailEmail").textContent = details.email;
    document.getElementById("patientDetailHospital").textContent = details.hospital;
    document.getElementById("patientDetailIdNumber").textContent = details.id_number;
    document.getElementById("patientDetailPhone").textContent = details.phone;
    document.getElementById("patientDetailAdmissionDate").textContent = details.admission_date;
    document.getElementById("patientDetailStatus").innerHTML = `<span class="status ${escapeAttribute(details.status_class)}">${escapeHtml(details.status_label)}</span>`;
    document.getElementById("patientDetailDoctor").textContent = details.doctor;
    document.getElementById("patientDetailWard").textContent = details.ward;
    document.getElementById("patientDiagnosis").value = details.diagnosis;
    document.getElementById("patientPrescription").value = details.prescription;
    document.getElementById("patientTests").value = details.tests;

    const timeline = document.getElementById("patientTimeline");
    timeline.innerHTML = (details.timeline || []).map((entry) => `
        <div class="timeline-item">
            <div class="timeline-date">${escapeHtml(entry.date)}</div>
            <div class="timeline-content">
                <h4>${escapeHtml(entry.title)}</h4>
                <p>${escapeHtml(entry.description)}</p>
            </div>
        </div>
    `).join("");

    detailElement.classList.add("active");
}

function hidePatientDetails() {
    adminDashboardState.selectedPatientId = null;
    document.getElementById("patientDetails").classList.remove("active");
}

function resetPatientSearch() {
    adminDashboardState.search.patientId = "";
    document.getElementById("patientId").value = "";
    renderPatientsTables();
}

function openPatientForm(patient) {
    adminDashboardState.editing.patientId = patient.id;
    document.getElementById("patientFormTitle").textContent = `Mise a jour de ${patient.name}`;
    document.getElementById("patientNom").value = patient.nom || "";
    document.getElementById("patientPrenom").value = patient.prenom || "";
    document.getElementById("patientEmailEdit").value = patient.email || "";
    document.getElementById("patientTelephoneEdit").value = patient.telephone || "";
    document.getElementById("patientBirthEdit").value = patient.date_of_birth || "";
    document.getElementById("patientGenderEdit").value = patient.gender || "";
    document.getElementById("patientBloodEdit").value = patient.blood_type || "";
    document.getElementById("patientEmergencyEdit").value = patient.emergency_contact || "";
    resetFeedback("patientFeedback");
    togglePanel("patientFormPanel", true);
    showSection("patients");
}

function closePatientForm() {
    adminDashboardState.editing.patientId = null;
    document.getElementById("patientUpdateForm").reset();
    resetFeedback("patientFeedback");
    togglePanel("patientFormPanel", false);
}

function openDoctorForm(doctor = null) {
    adminDashboardState.editing.doctorId = doctor?.id || null;
    document.getElementById("doctorFormTitle").textContent = doctor ? `Mise a jour de ${doctor.full_name}` : "Creation d un medecin";
    document.getElementById("submitDoctorFormBtn").innerHTML = doctor
        ? '<i class="fas fa-save"></i> Mettre a jour'
        : '<i class="fas fa-save"></i> Creer le medecin';
    document.getElementById("doctorCreateForm").reset();
    if (doctor) {
        document.getElementById("doctorNom").value = doctor.nom || "";
        document.getElementById("doctorPrenom").value = doctor.prenom || "";
        document.getElementById("doctorLogin").value = doctor.login || "";
        document.getElementById("doctorTelephone").value = doctor.telephone || "";
        document.getElementById("doctorEmail").value = doctor.email || "";
        document.getElementById("doctorSpecialization").value = doctor.specialization || "";
        document.getElementById("doctorLicense").value = doctor.license_number || "";
        document.getElementById("doctorCabinet").value = doctor.cabinet_id || "";
        document.getElementById("doctorPassword").required = false;
        document.getElementById("doctorPasswordConfirmation").required = false;
    } else {
        document.getElementById("doctorPassword").required = true;
        document.getElementById("doctorPasswordConfirmation").required = true;
    }
    resetFeedback("doctorFeedback");
    togglePanel("doctorFormPanel", true);
    showSection("doctors");
}

function closeDoctorForm() {
    adminDashboardState.editing.doctorId = null;
    document.getElementById("doctorCreateForm").reset();
    document.getElementById("doctorPassword").required = true;
    document.getElementById("doctorPasswordConfirmation").required = true;
    document.getElementById("doctorFormTitle").textContent = "Creation d un medecin";
    document.getElementById("submitDoctorFormBtn").innerHTML = '<i class="fas fa-save"></i> Creer le medecin';
    resetFeedback("doctorFeedback");
    togglePanel("doctorFormPanel", false);
}

function openSecretaryForm(secretary = null) {
    adminDashboardState.editing.secretaryId = secretary?.id || null;
    document.getElementById("secretaryFormTitle").textContent = secretary ? `Mise a jour de ${secretary.full_name}` : "Creation d une secretaire";
    document.getElementById("submitSecretaryFormBtn").innerHTML = secretary
        ? '<i class="fas fa-save"></i> Mettre a jour'
        : '<i class="fas fa-save"></i> Creer la secretaire';
    document.getElementById("secretaryCreateForm").reset();
    if (secretary) {
        document.getElementById("secretaryNom").value = secretary.nom || "";
        document.getElementById("secretaryPrenom").value = secretary.prenom || "";
        document.getElementById("secretaryLogin").value = secretary.login || "";
        document.getElementById("secretaryTelephone").value = secretary.telephone || "";
        document.getElementById("secretaryEmail").value = secretary.email || "";
        document.getElementById("secretaryOffice").value = secretary.office_number || "";
        document.getElementById("secretaryAssignment").value = secretary.assignment || "";
        document.getElementById("secretaryPassword").required = false;
        document.getElementById("secretaryPasswordConfirmation").required = false;
    } else {
        document.getElementById("secretaryPassword").required = true;
        document.getElementById("secretaryPasswordConfirmation").required = true;
    }
    resetFeedback("secretaryFeedback");
    togglePanel("secretaryFormPanel", true);
    showSection("secretaries");
}

function closeSecretaryForm() {
    adminDashboardState.editing.secretaryId = null;
    document.getElementById("secretaryCreateForm").reset();
    document.getElementById("secretaryPassword").required = true;
    document.getElementById("secretaryPasswordConfirmation").required = true;
    document.getElementById("secretaryFormTitle").textContent = "Creation d une secretaire";
    document.getElementById("submitSecretaryFormBtn").innerHTML = '<i class="fas fa-save"></i> Creer la secretaire';
    resetFeedback("secretaryFeedback");
    togglePanel("secretaryFormPanel", false);
}

async function handlePatientSubmit(event) {
    event.preventDefault();

    const patientId = adminDashboardState.editing.patientId;
    if (!patientId) {
        return;
    }

    const payload = {
        nom: document.getElementById("patientNom").value.trim(),
        prenom: document.getElementById("patientPrenom").value.trim(),
        email: document.getElementById("patientEmailEdit").value.trim(),
        telephone: document.getElementById("patientTelephoneEdit").value.trim(),
        date_of_birth: document.getElementById("patientBirthEdit").value || null,
        gender: document.getElementById("patientGenderEdit").value || null,
        blood_type: document.getElementById("patientBloodEdit").value.trim(),
        emergency_contact: document.getElementById("patientEmergencyEdit").value.trim(),
    };

    try {
        await apiCall(`/patients/${patientId}`, "PUT", payload);
        showFeedback("patientFeedback", "success", "Patient mis a jour avec succes.");
        await loadDashboardData();
        closePatientForm();
        showNotice("Les informations du patient ont ete mises a jour.", "success");
    } catch (error) {
        showFeedback("patientFeedback", "error", formatApiError(error));
    }
}

async function handleDoctorSubmit(event) {
    event.preventDefault();

    const doctorId = adminDashboardState.editing.doctorId;
    const payload = {
        nom: document.getElementById("doctorNom").value.trim(),
        prenom: document.getElementById("doctorPrenom").value.trim(),
        login: document.getElementById("doctorLogin").value.trim(),
        telephone: document.getElementById("doctorTelephone").value.trim(),
        email: document.getElementById("doctorEmail").value.trim(),
        specialization: document.getElementById("doctorSpecialization").value.trim(),
        license_number: document.getElementById("doctorLicense").value.trim(),
        cabinet_id: document.getElementById("doctorCabinet").value || null,
    };

    if (!doctorId) {
        payload.password = document.getElementById("doctorPassword").value;
        payload.password_confirmation = document.getElementById("doctorPasswordConfirmation").value;
    }

    try {
        await apiCall(doctorId ? `/doctors/${doctorId}` : "/doctors", doctorId ? "PUT" : "POST", payload);
        showFeedback("doctorFeedback", "success", doctorId ? "Medecin mis a jour avec succes." : "Medecin cree avec succes.");
        await loadDashboardData();
        closeDoctorForm();
        showNotice(doctorId ? "Le medecin a ete mis a jour." : "Le medecin a ete cree.", "success");
    } catch (error) {
        showFeedback("doctorFeedback", "error", formatApiError(error));
    }
}

async function handleSecretarySubmit(event) {
    event.preventDefault();

    const secretaryId = adminDashboardState.editing.secretaryId;
    const payload = {
        nom: document.getElementById("secretaryNom").value.trim(),
        prenom: document.getElementById("secretaryPrenom").value.trim(),
        login: document.getElementById("secretaryLogin").value.trim(),
        telephone: document.getElementById("secretaryTelephone").value.trim(),
        email: document.getElementById("secretaryEmail").value.trim(),
        office_number: document.getElementById("secretaryOffice").value.trim(),
        assignment: document.getElementById("secretaryAssignment").value.trim(),
    };

    if (!secretaryId) {
        payload.password = document.getElementById("secretaryPassword").value;
        payload.password_confirmation = document.getElementById("secretaryPasswordConfirmation").value;
    }

    try {
        await apiCall(secretaryId ? `/secretaries/${secretaryId}` : "/secretaries", secretaryId ? "PUT" : "POST", payload);
        showFeedback("secretaryFeedback", "success", secretaryId ? "Secretaire mise a jour avec succes." : "Secretaire creee avec succes.");
        await loadDashboardData();
        closeSecretaryForm();
        showNotice(secretaryId ? "La secretaire a ete mise a jour." : "La secretaire a ete creee.", "success");
    } catch (error) {
        showFeedback("secretaryFeedback", "error", formatApiError(error));
    }
}

async function resetPassword(type, id, name) {
    if (!window.confirm(`Reinitialiser le mot de passe de ${name} a 00000000 ?`)) {
        return;
    }

    try {
        await apiCall(`/admin/${type}s/${id}/reset-password`, "POST");
        showNotice(`Mot de passe de ${name} reinitialise a 00000000.`, "success");
    } catch (error) {
        showNotice(error.message || "Echec de la reinitialisation du mot de passe.", "error");
    }
}

async function deleteEntity(type, id, name) {
    if (!window.confirm(`Supprimer ${name} ? Cette action est definitive.`)) {
        return;
    }

    const endpointMap = {
        patient: `/patients/${id}`,
        doctor: `/doctors/${id}`,
        secretary: `/secretaries/${id}`,
    };

    try {
        await apiCall(endpointMap[type], "DELETE");
        if (type === "patient" && adminDashboardState.selectedPatientId === id) {
            hidePatientDetails();
        }
        await loadDashboardData();
        showNotice(`${name} a ete supprime(e) avec succes.`, "success");
    } catch (error) {
        showNotice(error.message || "Suppression impossible.", "error");
    }
}

function getSelectedPatient() {
    return findPatientById(adminDashboardState.selectedPatientId);
}

function findPatientById(patientId) {
    return (adminDashboardState.data?.patients || []).find((patient) => Number(patient.id) === Number(patientId)) || null;
}

function findDoctorById(doctorId) {
    return (adminDashboardState.data?.doctors || []).find((doctor) => Number(doctor.id) === Number(doctorId)) || null;
}

function findSecretaryById(secretaryId) {
    return (adminDashboardState.data?.secretaries || []).find((secretary) => Number(secretary.id) === Number(secretaryId)) || null;
}

function togglePanel(panelId, shouldOpen) {
    const panel = document.getElementById(panelId);
    if (!panel) {
        return;
    }

    panel.classList.toggle("hidden", !shouldOpen);
    if (shouldOpen) {
        panel.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

function renderSimpleTable(bodyId, rows, renderer, colspan, emptyMessage) {
    const tbody = document.getElementById(bodyId);
    if (!tbody) {
        return;
    }

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="${colspan}">${escapeHtml(emptyMessage)}</td></tr>`;
        return;
    }

    tbody.innerHTML = rows.map(renderer).join("");
}

function showNotice(message, type = "success") {
    const notice = document.getElementById("dashboardNotice");
    notice.className = `notice ${type}`;
    notice.textContent = message;
    notice.style.display = "block";
}

function showFeedback(id, type, html) {
    const element = document.getElementById(id);
    element.className = `feedback-box ${type}`;
    element.innerHTML = html;
}

function resetFeedback(id) {
    const element = document.getElementById(id);
    element.className = "feedback-box";
    element.innerHTML = "";
}

function formatApiError(error) {
    if (error?.data?.errors && typeof error.data.errors === "object") {
        return Object.values(error.data.errors)
            .flat()
            .map((message) => `<div>${escapeHtml(String(message))}</div>`)
            .join("");
    }

    return escapeHtml(error.message || "Une erreur est survenue.");
}

function formatNumber(value) {
    return new Intl.NumberFormat("fr-FR").format(Number(value || 0));
}

function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function escapeAttribute(value) {
    return escapeHtml(value).replace(/`/g, "&#096;");
}

const clinicId = document.querySelector('[data-clinic-id]')?.dataset.clinicId;
const userId = document.querySelector('[data-user-id]')?.dataset.userId;
const appointments = new Map(
    [...document.querySelectorAll('[data-appointment-id]')].map(row => [String(row.dataset.appointmentId), row]),
);

const renderAppointment = appointment => {
    const id = String(appointment.id);

    let row = appointments.get(id);

    if (!row) {
        row = document.createElement('li');
        row.dataset.appointmentId = id;
        row.innerHTML = `<strong>#${id}</strong> <span data-status></span>`;
        document.querySelector('[data-appointments]')?.append(row);
        appointments.set(id, row);
    }

    row.querySelector('[data-status]').textContent = appointment.status;
};

if (clinicId && window.Echo) {
    window.Echo.private(`clinic.${clinicId}`)
        .listen('.appointment.created', ({ appointment }) => renderAppointment(appointment))
        .listen('.appointment.status.changed', ({ appointment }) => renderAppointment(appointment))
        .listen('.appointment.cancelled', ({ appointment }) => renderAppointment(appointment));

    window.Echo.join(`agenda.${clinicId}`)
        .here(users => document.querySelector('[data-online-count]').textContent = users.length)
        .joining(() => document.querySelector('[data-online-count]').textContent++)
        .leaving(() => document.querySelector('[data-online-count]').textContent--);

    if (userId) {
        window.Echo.private(`App.Models.User.${userId}`)
            .notification(notification => {
                const item = document.createElement('li');
                item.textContent = notification.message;
                document.querySelector('[data-notifications]')?.prepend(item);
            });
    }

    window.addEventListener('beforeunload', () => {
        window.Echo.leave(`clinic.${clinicId}`);
        window.Echo.leave(`agenda.${clinicId}`);
        window.Echo.leave(`App.Models.User.${userId}`);
    }, { once: true });
}

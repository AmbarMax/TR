export function prefillshow(form, model) {
    form.find('.show-name').text(model.name)
    form.find('.show-email').text(model.email)
    form.find('.show-username').text(model.username)
    form.find('.show-phone').text(model.phone_number)
    form.find('.show-birth-date').text(model.format_date_of_birth)
    form.find('.show-email-verified-at').text(model.format_email_verified_at)
    if (model.roles && model.roles.length > 0) {
        const rolesList = model.roles.map(role => role.name).join(', ');
        form.find('.show-roles').text(rolesList);
    } else {
        form.find('.show-roles').text('-');
    }
}

export function prefillshowtrophy(form, model) {
    form.find('.show-title').text(model.name)
    form.find('.show-price').text(model.price)
    form.find('.show-type').text(model.typeName)
    form.find('.show-quantity').text(model.quantity)
    form.find('.show-key-name').text(model.key?.name || '-');
    form.find('.show-receive').text(model.receive)
    form.find('.show-description').text(model.description)
    if (model.expiration_date !== null){
        form.find('.show-expiration-date').text(model.expiration_date_show_format)
    } else {
        form.find('.show-expiration-date').text('-')
    }
}

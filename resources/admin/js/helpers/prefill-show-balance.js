export function prefill(form, model, url) {
        form.find('input').each(function () {
            let name = $(this).attr('name')
            if (typeof model.balance === 'object' && name in model.balance) {
                const currencyData = model.balance[name];
                $(this).val(currencyData.amount);
            }
        })
    form.attr('action', url)
}

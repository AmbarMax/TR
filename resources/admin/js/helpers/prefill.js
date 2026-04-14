export function prefill(form, model, url) {
    form.find('input').each(function () {
        let name = $(this).attr('name')
        if($(this).attr('type') === 'text'){
            if($(this).attr('data-format')) {
                $(this).val(model[$(this).attr('data-format')])
            } else if (model.hasOwnProperty(name)) {
                $(this).val(model[name])
            }
        }
    })
    form.find('textarea').each(function () {
        let name = $(this).attr('name')
        if (model.hasOwnProperty(name)) {
            $(this).val(model[name])
        }
    })
    form.find('select').each(function () {
        let name = $(this).attr('name');
        let selectPrefillKey = $(this).data('prefill-key')
        if(name.toString().includes('[]')){
            name = name.replace('[]','')
            if (model.hasOwnProperty(name)) {
                let values = [];
                $.each(model[name], function(index, value) {
                    values.push(value[selectPrefillKey])
                });
                $(this).val(values).change();
            }
        }else{
            if (model.hasOwnProperty(name)) {
                $(this).val(model[name]).change();
            }
        }
    })

    let rolePicker = form.find('select[name="roles"]');
    if (model.hasOwnProperty('roles') && model.roles.length > 0) {
        rolePicker.val(model.roles[0].id);
    } else {
        rolePicker.val('');
    }

    form.attr('action', url)
}

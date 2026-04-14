export function validation(form, errors, textError) {
    if(errors) {
        console.log('here');
        $.each(form.elements, function(index, elem){
            if($(elem).is('input') || $(elem).is('select') || $(elem).is('textarea')){
                if($(elem).attr('type') !== 'hidden'){
                    if(!$(elem).hasClass('select2-search__field')) {
                        if(errors.hasOwnProperty(clearStr($(elem).attr('name')))){
                            $(elem).addClass('is-invalid');
                            if(textError){
                                $(elem).parent().closest('.form-group').find('.error-text').text(errors[clearStr($(elem).attr('name'))][0]).show();
                            }
                            if($(elem).hasClass('js-validate-select')){
                                $(elem).next().addClass('is-invalid')
                            }
                        }else {
                            $(elem).removeClass('is-invalid');
                            if(textError){
                                $(elem).parent().closest('.form-group').find('.error-text').text('').hide();
                            }
                            if($(elem).hasClass('js-validate-select')){
                                $(elem).next().removeClass('is-invalid')
                            }
                        }
                    }
                }
            }
        });
    } else {
    }
}

function clearStr(str) {
    if(str) {
        let arr = str.split('');
        return arr.filter(char => {
            return !(char === '[' || char === ']');
        }).join('');
    }
}

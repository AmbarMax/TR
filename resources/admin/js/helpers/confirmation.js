import Swal from 'sweetalert2'

$(document).ready(function () {
    $('.js-confirmation-swal').on('click', function () {
        let el = $(this),
            form = $('.js-confirmation-form'),
            url = el.attr('data-url'),
            message = el.attr('data-message'),
            method = el.attr('data-method'),
            field = el.attr('data-field'),
            value = el.attr('data-value');
        form.attr('action', url);
        form.append(`<input type="hidden" name="_method" value="${method}"/>`);
        if(field && value){
            form.append(`<input type="hidden" name="${field}" value="${value}"/>`);
        }
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            buttonsStyling: false,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            customClass: {
                closeButton: 'btn btn-outline-secondary btn-round',
                confirmButton: 'btn btn-outline-primary btn-round',
                denyButton: 'btn btn-outline-secondary btn-round',
                cancelButton: 'btn btn-outline-secondary btn-round mr-2',
                buttonsStyling: false,
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit()
            }
        })
    });
})

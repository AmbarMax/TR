/** Packages */
import './bootstrap';
import 'toastr';
import 'jquery-validation';
import 'jquery-mask-plugin';
import 'flag-icons';
import flatpickr from 'flatpickr';
import 'moment';
import './helpers/confirmation.js'
import feather from 'feather-icons'
import Swal from 'sweetalert2';
import 'select2';

// window.Swal = swal;

import { notification } from "./helpers/notification";

import.meta.glob([
    '../images/**',
    '../../assets/images/**',
    '../../views/api/mail/images/**',
]);

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // Change theme color
    $('.js-change-theme').on('click', function () {
        $.ajax({
            type: 'get',
            url: $('html').data('theme-url'),
        })
    })
    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });
    setTimeout(function () {
        $('.global-loader').hide()
    }, 300)

    let notification = $('#session-notification-data')
    if(notification.length > 0){
        Swal.fire({
            title: notification.data('title'),
            text: notification.data('message'),
            icon: notification.data('type'),
            position: 'top-end',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
        })
    }
    $('.js-toggle-menu').click(function(event) {
        $('body').toggleClass('menu-open');
        $('.sidenav-overlay').toggleClass('show');
    });
})


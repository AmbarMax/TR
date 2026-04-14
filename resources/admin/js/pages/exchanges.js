import {prefill} from "../helpers/prefill.js";
import {prefillshow} from "../helpers/prefill-show.js";
import flatpickr from "flatpickr";
import {notification} from "../helpers/notification.js";
$(document).ready(function () {


    let usersTable = $('#exchanges-index-table');

    let userDataTable = usersTable.DataTable({
        pageLength: -1,
        order: [],
        columns: [
            null,
            null,
            null,
            null,
            null,
            null,
            { searchable: false},
            { searchable: false, sorting: false }
        ],
        language: {
            emptyTable: 'No data available',
            zeroRecords: 'No data available',
            search: 'Search',
            lengthMenu: 'Show _MENU_ entries',
            info: "Show from _START_ to _END_ of _TOTAL_ entries",
            infoFiltered: "(filtered from 79 entries)",
            infoEmpty: "Showed from 0 to 0 of 0 entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
        }
    });

    setTimeout(function() {
        userDataTable.page.len(10).draw();
    }, 1);

    let editUserForm = $('#edit-exchange-form');

    let editUserModal = $('#edit-exchange-modal');

    $('.js-edit-exchange-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                let model = response.model;
                editUserForm.find('.show-uru').text(model.input_amount + ' ' + model.input_currency);
                editUserForm.find('.show-cryptocurrency').text(model.output_amount + ' ' + model.output_currency);
                editUserForm.find('.show-wallet').text(model.wallet_number);
                document.getElementById('fp-role-picker-exchange-status').value = model.status;
                editUserForm.attr('action', response.update_url)

                editUserModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })

});

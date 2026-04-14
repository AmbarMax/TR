import {prefill} from "../helpers/prefill.js";
import {prefillshow} from "../helpers/prefill-show.js";
import flatpickr from "flatpickr";
import {notification} from "../helpers/notification.js";
$(document).ready(function () {

    let usersTable = $('#admins-index-table');

    let userDataTable = usersTable.DataTable({
        pageLength: -1,
        order: [],
        columns: [
            null,
            null,
            null,
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

    let editUserForm = $('#edit-admin-form');
    let showUserForm = $('#show-admin-form');

    let editUserModal = $('#edit-admin-modal');
    let showUserModal = $('#show-admin-modal');

    $('.js-edit-admin-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                prefill(editUserForm, response.model, response.update_url)
                if (response.model.super_admin == 1){
                    document.getElementById('super_admin_checkbox').checked = true;
                }
                editUserModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })




});

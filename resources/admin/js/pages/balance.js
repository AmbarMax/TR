import {prefill} from "../helpers/prefill-show-balance.js";
import {notification} from "../helpers/notification.js";

$(document).ready(function () {

    let balanceTable = $('#balance-index-table');

    let balanceDataTable = balanceTable.DataTable({
        pageLength: -1,
        order: [],
        columns: [
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
        balanceDataTable.page.len(10).draw();
    }, 1);


    let editBalanceModal = $('#edit-balance-modal');
    let editBalanceForm = $('#edit-balance-form');

    $('.js-edit-balance-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                prefill(editBalanceForm, response.model, response.update_url)
                editBalanceModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })



});

import {prefill} from "../helpers/prefill-show-balance.js";
import {notification} from "../helpers/notification.js";

$(document).ready(function () {

    $('.js-edit-trophy-select').select2({
        multiple: true,
        placeholder: 'Select badges',
    });
    $(".js-edit-trophy-select").find("option[value=''][disabled][selected]").remove();

    let assignmentOfTrophiesTable = $('#assignment-of-trophies-index-table');

    let assignmentOfTrophiesDataTable = assignmentOfTrophiesTable.DataTable({
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
        assignmentOfTrophiesDataTable.page.len(10).draw();
    }, 1);


    let editAssignmentOfTrophiesModal = $('#edit-assignment-of-trophies-modal');
    let editAssignmentOfTrophiesForm = $('#assignment-of-trophies');

    $('.js-edit-assignment-of-trophies-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                prefill(editAssignmentOfTrophiesForm, response.model, response.update_url)

                var idArray = response.model.trophies.map(function(trophy) {
                    return trophy.id;
                });
                $('.js-edit-trophy-select').val(idArray);
                $('.js-edit-trophy-select').trigger('change');

                editAssignmentOfTrophiesModal.modal('show');
                console.log(response.model);
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })



});

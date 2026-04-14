import {notification} from "../helpers/notification.js";
import {prefillshowtrophy} from "../helpers/prefill-show-trophy.js";

$(document).ready(function () {

    let keysTable = $('#keys-index-table');

    let keysDataTable = keysTable.DataTable({
        pageLength: -1,
        order: [],
        columns: [
            null,
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
        keysDataTable.page.len(10).draw();
    }, 1);

    $('.js-create-key-select').select2({
        multiple: true,
        placeholder: 'Select badges',
    });
    $(".js-create-key-select").find("option[value=''][disabled][selected]").remove();


    $(".image-input-closed").on("change", function (event) {
        const fileInput = event.target;
        if ($(this).val() == ""){
            notification('error', 'Error!','Empty file');
            return false;
        }
        let regex = /\.(jpg|jpeg|png)$/i;
        let tgt = event.target,
            files = tgt.files,
            image = files[0].name,
            size = files[0].size;
        if(parseInt(size/1000) > 10000){
            notification('error', 'Error!','File size too large');
            return false;
        }
        const imagePreview = $(fileInput).closest(".form-group").find(".image-selected-closed")[0];
        if (fileInput.files && fileInput.files[0] && image.search(regex) != -1 && FileReader) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = reader.result;
            };
            reader.readAsDataURL(fileInput.files[0]);
        } else {
            notification('error', 'Error!','The file does not match');
            return false;
        }
    });

    $('.js-upload-key-closed').on('click', function () {
        $(this).parent().parent().find(".image-input-closed").click();
    })


    let showTrophyModal = $('#show-key-modal');
    let currentId = 0;

    let editTrophyModal = $('#edit-key-modal');


    $('.js-show-key-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                console.log(response.model)
                $(".key-closed").attr("src", `/storage/keys/${response.model.image}`);

                prefillshowtrophy(showTrophyModal, response.model)

                showTrophyModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })

    $('.js-edit-key-btn').on('click', function () {
        $(".image-selected").each(function() {
            $(this).attr("src", "");
        });
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                $(".key-closed").attr("src", `/storage/keys/${response.model.image}`);
                editTrophyModal.find('.show-title').val(response.model.name)
                editTrophyModal.find('.show-quantity').val(response.model.quantity)
                editTrophyModal.find('.show-description').text(response.model.description)
                var form = document.getElementById("edit-key-form");
                var lastSlashIndex = form.getAttribute("action").lastIndexOf('/');
                var newValue = form.getAttribute("action").substring(0, lastSlashIndex + 1) + response.model.id;
                form.setAttribute("action", newValue);

                var selectElement = document.getElementById('fp-role-picker-key-status');
                if (selectElement) {
                    var idArray = response.model.items.map(function(badge) {
                        return badge.id;
                    });

                    $('.js-create-key-select').val(idArray);
                    $('.js-create-key-select').trigger('change');
                }

                showTrophyModal.modal('hide');
                editTrophyModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })

    $('.js-key-create').on('click', function () {
        $(".image-selected").each(function() {
            $(this).attr("src", "");
        });
    })
});






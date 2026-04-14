import {notification} from "../helpers/notification.js";
import {prefillshowtrophy} from "../helpers/prefill-show-trophy.js";
import flatpickr from "flatpickr";

$(document).ready(function () {

    $('.js-create-key-select').select2({
        multiple: false,
        placeholder: 'Select key',
    });

    $('.js-create-chest-select').select2({
        multiple: true,
        placeholder: 'Select badges',
    });
    $(".js-create-chest-select").find("option[value=''][disabled][selected]").remove();

    flatpickr(".js-expiration-date-at-picker", {
        defaultDate: 'today',
        dateFormat: 'M j, Y',
        allowInput: true,
    });


    let chestsTable = $('#chests-index-table');

    let chestsDataTable = chestsTable.DataTable({
        pageLength: -1,
        order: [],
        columns: [
            null,
            null,
            null,
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
        chestsDataTable.page.len(10).draw();
    }, 1);

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

    $(".image-input-opened").on("change", function (event) {
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
        const imagePreview = $(fileInput).closest(".form-group").find(".image-selected-opened")[0];
        console.log(imagePreview)
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

    $('.js-upload-chest-closed').on('click', function () {
        $(this).parent().parent().find(".image-input-closed").click();
    })

    $('.js-upload-chest-opened').on('click', function () {
        $(this).parent().parent().find(".image-input-opened").click();
    })

    let showTrophyModal = $('#show-chest-modal');
    let currentId = 0;

    let editTrophyModal = $('#edit-chest-modal');


    $('.js-show-chest-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                $(".chest-closed").attr("src", `/storage/chests/${response.model.image_closed}`);
                $(".chest-opened").attr("src", `/storage/chests/${response.model.image_open}`);

                prefillshowtrophy(showTrophyModal, response.model)
                currentId = response.model.id;
                var trophyShowElement = document.querySelector('.js-chest-show');

                while (trophyShowElement.firstChild) {
                    trophyShowElement.removeChild(trophyShowElement.firstChild);
                }

                response.model.items.forEach(function(value) {
                    var newDiv = document.createElement('div');
                    newDiv.className = 'badge badge-light-primary';
                    newDiv.textContent = value.name;
                    trophyShowElement.appendChild(newDiv);
                });

                showTrophyModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })

    $('.js-edit-chest-btn').on('click', function () {
        $(".image-selected").each(function() {
            $(this).attr("src", "");
        });
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                $(".chest-closed").attr("src", `/storage/chests/${response.model.image_closed}`);
                $(".chest-opened").attr("src", `/storage/chests/${response.model.image_open}`);
                if (response.model.expiration_date !== null){
                    document.getElementById('fp-expiration-date-at-picker-edit').value = response.model.expiration_date_show_format;
                } else {
                    document.getElementById('fp-expiration-date-at-picker-edit').value = '';
                }
                editTrophyModal.find('.show-title').val(response.model.name)
                editTrophyModal.find('.show-quantity').val(response.model.quantity)
                editTrophyModal.find('.show-description').text(response.model.description)
                var form = document.getElementById("edit-chest-form");
                var lastSlashIndex = form.getAttribute("action").lastIndexOf('/');
                var newValue = form.getAttribute("action").substring(0, lastSlashIndex + 1) + response.model.id;
                form.setAttribute("action", newValue);

                var selectElement = document.getElementById('fp-role-picker-chest-status');
                if (selectElement) {
                    var idArray = response.model.items.map(function(badge) {
                        return badge.id;
                    });

                    $('.js-create-chest-select').val(idArray);
                    $('.js-create-chest-select').trigger('change');
                }

                var selectElementKey = document.getElementById('fp-role-picker-key-status');
                if (selectElementKey) {
                    $('.js-create-key-select').val(response.model.key.id);
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

    $('.js-chest-create').on('click', function () {
        $(".image-selected").each(function() {
            $(this).attr("src", "");
        });
    })
});






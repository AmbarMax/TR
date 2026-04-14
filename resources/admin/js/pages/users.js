import {prefill} from "../helpers/prefill.js";
import {prefillshow} from "../helpers/prefill-show.js";
import flatpickr from "flatpickr";
import {notification} from "../helpers/notification.js";
$(document).ready(function () {

    flatpickr(".js-birth-date-picker", {
        allowInput: true,
        dateFormat: 'M j, Y'
    });

    flatpickr(".js-email-verified-at-picker", {
        defaultDate: 'today',
        dateFormat: 'M j, Y',
        allowInput: true,
    });

    let usersTable = $('#users-index-table');

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

    let editUserForm = $('#edit-user-form');
    let showUserForm = $('#show-user-form');

    let editUserModal = $('#edit-user-modal');
    let showUserModal = $('#show-user-modal');

    $('.js-edit-user-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                prefill(editUserForm, response.model, response.update_url)
                if (response.model.google2fa_status === 0){
                    document.getElementById('2fa-active').style.display = 'none';
                    document.getElementById('2fa-inactive').style.display = 'unset';
                } else {
                    document.getElementById('2fa-inactive').style.display = 'none';
                    document.getElementById('2fa-active').setAttribute('data-id', response.model.id);
                    document.getElementById('2fa-active').style.display = 'unset';
                }
                if (response.model.avatar === null){
                    $(".user-avatar").attr("src", `/admin/img/avatar/default-profile-img.png`);
                } else {
                    $(".user-avatar").attr("src", `/storage/users/${response.model.id}/avatar/${response.model.avatar}.jpg`);
                }
                if (response.model.background === null) {
                    $(".user-background").attr("src", `/images/background/default-background-img.svg`);
                } else {
                    $(".user-background").attr("src", `/storage/users/${response.model.id}/background/${response.model.background}`);
                }
                // editUserForm.find('select[name="role"]').val(response.model.roles.id);

                editUserModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })

    $('.js-show-user-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {

                prefillshow(showUserModal, response.model)
                if (response.model.avatar === null){
                    $(".user-avatar").attr("src", `/admin/img/avatar/default-profile-img.png`);
                } else {
                    $(".user-avatar").attr("src", `/storage/users/${response.model.id}/avatar/${response.model.avatar}.jpg`);
                }
                if (response.model.background === null) {
                    $(".user-background").attr("src", `/images/background/default-background-img.svg`);
                } else {
                    $(".user-background").attr("src", `/storage/users/${response.model.id}/background/${response.model.background}`);
                }
                showUserModal.modal('show');
            },
            error: function(xhr) {
                let response = JSON.parse(xhr.responseText);
                notification(response.type, response.message);
            }
        })
    })

    $('.js-disable-2fa').on('click', function () {
        let userId = $(this).parent()[0].getAttribute('data-id');
        let url =
        $.ajax({
            type: 'GET',
            url: `/admin/users/disable2fa/${userId}`,
            success: function(response) {
                document.getElementById('2fa-active').style.display = 'none';
                document.getElementById('2fa-inactive').style.display = 'unset';
            },
        })
    })

    $('.js-upload-image-avatar').on('click', function () {
        $("#avatar-image").find(".image-input").click();
    })

    $('.js-upload-image-background').on('click', function () {
        $("#background-image").find(".image-input").click();
    })

    $('.js-upload-image-avatar-update').on('click', function () {
        $("#avatar-image-update").find(".image-input").click();
    })

    $('.js-upload-image-background-update').on('click', function () {
        $("#background-image-update").find(".image-input").click();
    })

    $(".image-input").on("change", function (event) {
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
        const imagePreview = $(fileInput).closest(".form-group").find(".image-selected")[0];
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

    $(".js-user-create").on('click', function () {
        document.getElementById('user-avatar').setAttribute("src", `/images/avatar/default-profile-img.png`);
        document.getElementById('user-background').setAttribute("src", `/images/background/default-background-img.svg`);
    });

    // $(document).ready(function() {
    //     // Найдем форму по ее идентификатору
    //     let form = $('#edit-user-form');
    //
    //     // Повесьте обработчик события на отправку формы
    //     form.submit(function(event) {
    //         // Остановим стандартное действие отправки формы
    //         event.preventDefault();
    //
    //         // Соберем данные формы
    //         let formData = new FormData(this);
    //
    //         // Выведем данные в консоль для отладки
    //         for (var pair of formData.entries()) {
    //             console.log(pair[0]+ ', ' + pair[1]);
    //         }
    //     });
    // });

});

import {notification} from "../helpers/notification.js";
import {prefillshowtrophy} from "../helpers/prefill-show-trophy.js";

$(document).ready(function () {

    $('.js-create-key-select').select2({
        multiple: false,
        placeholder: 'Select key',
    });

    $('.js-edit-key-select').select2({
        multiple: false,
        placeholder: 'Select key',
    });

    $('.js-create-trophy-select').select2({
        multiple: true,
        placeholder: 'Select badges',
    });
    $(".js-create-trophy-select").find("option[value=''][disabled][selected]").remove();


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

    $('.js-upload-trophy').on('click', function () {
        $(this).parent().parent().find(".image-input").click();
    })

    let showTrophyModal = $('#show-trophy-modal');
    let currentId = 0;

    let editTrophyModal = $('#edit-trophy-modal');


    $('.js-show-trophy-btn').on('click', function () {
        let url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                $(".trophy").attr("src", `/storage/trophies/${response.model.image}`);
                prefillshowtrophy(showTrophyModal, response.model)
                currentId = response.model.id;
                var currentDataUrl = document.getElementById("delete-btn").getAttribute("data-url");
                var lastSlashIndex = currentDataUrl.lastIndexOf('/');
                var newValue = currentDataUrl.substring(0, lastSlashIndex + 1) + response.model.id;
                document.getElementById("delete-btn").setAttribute("data-url", newValue);

                var trophyShowElement = document.querySelector('.js-trophy-show');

                while (trophyShowElement.firstChild) {
                    trophyShowElement.removeChild(trophyShowElement.firstChild);
                }

                response.model.badges.forEach(function(value) {
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

    $('.js-edit-trophy-btn').on('click', function () {
        $(".image-selected").each(function() {
            $(this).attr("src", "");
        });
        let url = $(this).data('url');
        var newURL = url.substring(0, url.lastIndexOf('/') + 1) + currentId;
        $.ajax({
            type: 'GET',
            url: newURL,
            success: function(response) {
                $(".trophy").attr("src", `/storage/trophies/${response.model.image}`);
                editTrophyModal.find('.show-title').val(response.model.name)
                editTrophyModal.find('.show-price').val(response.model.price)
                editTrophyModal.find('.show-receive').val(response.model.receive)
                editTrophyModal.find('.show-description').text(response.model.description)
                var form = document.getElementById("edit-trophy-form");
                var lastSlashIndex = form.getAttribute("action").lastIndexOf('/');
                var newValue = form.getAttribute("action").substring(0, lastSlashIndex + 1) + response.model.id;
                form.setAttribute("action", newValue);

                var idArray = response.model.badges.map(function(badge) {
                    return badge.id;
                });
                $('.js-create-trophy-select').val(idArray);
                $('.js-create-trophy-select').trigger('change');

                var selectElementKey = document.getElementById('trophies-edit-key');
                if (selectElementKey && response.model.key !== null) {
                    $('.js-edit-key-select').val(response.model.key.id);
                    $('.js-edit-key-select').trigger('change');

                    var input = document.getElementById('flexSwitchCheckKeyEdit');
                    input.checked = true;
                } else {
                    document.getElementById('max-nft-key-edit').classList.add('hidden');
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

    $('.js-trophy-create').on('click', function () {
        $(".image-selected").each(function() {
            $(this).attr("src", "");
        });
    })
});






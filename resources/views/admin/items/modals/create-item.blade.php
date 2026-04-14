<div class="modal show new-item-modal fade" id="create-item-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="col-12 text-center">
                <h2>Create new Item</h2>
            </div>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <form action="{{route('admin.items.store')}}" method="POST" id="crate-item-form"
                      class="add-new-item row g-3 fv-plugins-bootstrap5 fv-plugins-framework"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group" id="item-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-item-image">
                                <div class="item-image-alt"></div>
                                <img id="image" class="item image-selected item-image" alt="">
                                <input hidden="" type="file" name="image" class="image-input input-img">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-item">Set Image</button>
                        </div>
                    </div>

                    <div class="col-12 mb-2 col-md-8 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyTitle"> Title *</label>
                        <div class="input-group input-group-merge">
                            <input required type="text" class="form-control" name="name"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('name'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-12 mb-2 col-md-4">
                        <label for="fp-role-picker">Type</label>
                        <select id="fp-role-picker-exchange-status" class="form-control" name="status">
                            <option value="0">
                                Skin
                            </option>
                            <option value="1">
                                Discount
                            </option>
                        </select>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyDescription"> Description</label>
                        <div class="input-group input-group-merge">
                            <textarea type="text" class="form-control" name="description"></textarea>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('description'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Create</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    .item-image {
        object-fit: cover;
        overflow: hidden;
        border-radius: 5px;
        background-color: lightgray;
        display: none;
    }
    .item-image-alt {
        height: 200px;
        width: 200px;
        border-radius: 5px;
        background-color: lightgray;
        display: block;
    }

    .item {
        height: 200px; width: 200px; border-radius: 0px;
    }
    .btn-close {
        position: absolute;
        scale: 2;
        border: unset;
        background-color: unset;
        margin: 0!important;
        right: 15px;
        top: 15px;
        box-shadow: unset!important;
    }
</style>
<script>
    function maxSupplyDisplay() {
        let isNft = document.getElementById('flexSwitchCheckDefault').checked;
        let maxSupplyInput = document.getElementById('max-nft-supply');
        if (isNft) {
            maxSupplyInput.classList.remove('hidden');
            maxSupplyInput.setAttribute('required', 'required');
        } else {
            maxSupplyInput.classList.add('hidden');
            maxSupplyInput.removeAttribute('required');
        }
    }
    const inputImg = document.querySelector('.input-img');

    inputImg.addEventListener('change', () => {
        document.querySelector('.item-image-alt').style.display = 'none';
        document.querySelector('.item-image').style.display = 'block';
    });
</script>

<div class="modal show new-key-modal fade" id="create-key-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="col-12 text-center">
                <h2>Create new key</h2>
            </div>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <form action="{{route('admin.keys.store')}}" method="POST" id="crate-key-form"
                      class="add-new-key row g-3 fv-plugins-bootstrap5 fv-plugins-framework"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group" id="key-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-key-image">
                                {{--<div class="key-image-alt"></div>--}}
                                <img style="display: block" id="image" class="key image-selected-closed key-image" alt="">
                                <input hidden="" type="file" name="image" class="image-input-closed input-img">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-key-closed">Set Key image</button>
                        </div>
                    </div>

                    <div class="col-8 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreatekeyTitle"> Name *</label>
                        <div class="input-group input-group-merge">
                            <input required type="text" class="form-control" name="name"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('name'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-4 mb-2 fv-plugins-icon-container" id="max-nft-supply">
                        <label class="form-label" for="modalCreatekeyPrice"> Quantity *</label>
                        <div class="input-group input-group-merge">
                            <input required type="number" class="form-control" name="quantity" value="1"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('max_supply'){{ $message }}@enderror</strong>
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
    .key-image {
        object-fit: cover;
        overflow: hidden;
        border-radius: 5px;
        background-color: lightgray;
        display: none;
    }
    .key-image-alt {
        height: 200px;
        width: 200px;
        border-radius: 5px;
        background-color: lightgray;
        display: block;
    }

    .key {
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
        document.querySelector('.key-image-alt').style.display = 'none';
        document.querySelector('.key-image').style.display = 'block';
    });
</script>

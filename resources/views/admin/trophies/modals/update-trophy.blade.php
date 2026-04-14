<div class="modal show new-trophy-modal fade" id="edit-trophy-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="col-12 text-center">
                <h2>Update Trophy</h2>
            </div>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <form action="{{route('admin.trophies.update', ['id' => $trophy->id])}}" method="POST" id="edit-trophy-form"
                      class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework"
                      enctype="multipart/form-data">

                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group" id="trophy-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-trophy-image">
                                <img id="image" class="trophy image-selected trophy-img">
                                <input hidden="" type="file" name="image" class="image-input">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-trophy">Set Image</button>
                        </div>
                    </div>

                    <div class="col-7 form-group" style="align-content: center; margin-top: 1rem;" id="trophy-is-nft">
                        <div class="form-check form-switch swal2-checkbox" onclick="maxNFTKey()">
                            <input name="is_key" class="form-check-input" type="checkbox" id="flexSwitchCheckKeyEdit">
                            <label class="form-check-label pl-1" for="flexSwitchCheckKey">NFT Key</label>
                        </div>
                    </div>
                    <div class="col-3 mb-2 fv-plugins-icon-container" id="max-nft-key-edit">

                        <label class="form-label" for="modalCreatechestBadges"> Key *</label>

                        <select id="trophies-edit-key" class="js-edit-key-select" name="key">
                            <option value="" disabled selected>Select items</option>
                            @foreach($keys as $key)
                                <option value="{{$key->id}}">{{$key->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyTitle">Title *</label>
                        <div class="input-group input-group-merge">
                            <input required type="text" class="form-control trophy-data show-title" name="name"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">
                            @error('name'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyPrice">Price(ambars) *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"></span>
                            </div>
                            <input required type="number" class="form-control trophy-data show-price" name="price"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">
                            @error('receive'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyPrice">Receive(uru) *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"></span>
                            </div>
                            <input required type="number" class="form-control trophy-data show-receive" name="receive"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">
                            @error('price'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyBadges"> Badges *</label>

                        <select class="js-create-trophy-select" required name="badges[]">
                            <option value="" disabled selected>Select badges</option>
                            @foreach($badges as $badge)
                                <option value="{{$badge->id}}">{{$badge->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyDescription"> Description</label>
                        <div class="input-group input-group-merge">
                            <textarea type="text" class="form-control show-description" name="description"></textarea>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('description'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    /*img {*/
    /*    object-fit: cover;*/
    /*}*/
    .trophy {
        height: 200px; width: 200px; border-radius: 5px;
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
    function maxNFTKey() {
        console.log('change')
        let isNft = document.getElementById('flexSwitchCheckKeyEdit').checked;
        let maxSupplyInput = document.getElementById('max-nft-key-edit');
        if (isNft) {
            maxSupplyInput.classList.remove('hidden');
            maxSupplyInput.setAttribute('required', 'required');
        } else {
            maxSupplyInput.classList.add('hidden');
            maxSupplyInput.removeAttribute('required');
        }
        console.log(maxSupplyInput)

    }
</script>


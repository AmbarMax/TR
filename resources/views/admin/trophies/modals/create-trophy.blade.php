<div class="modal show new-trophy-modal fade" id="create-trophy-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="col-12 text-center">
                <h2>Create new Trophy</h2>
            </div>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <form action="{{route('admin.trophies.store')}}" method="POST" id="crate-trophy-form"
                      class="add-new-trophy row g-3 fv-plugins-bootstrap5 fv-plugins-framework"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group" id="trophy-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-trophy-image">
                                <div class="trophy-image-alt"></div>
                                <img id="image" class="trophy image-selected trophy-image" alt="">
                                <input hidden="" type="file" name="image" class="image-input input-img">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-trophy">Set Image</button>
                        </div>
                    </div>

                    <div class="col-7 form-group" style="align-content: center; margin-top: 1rem;" id="trophy-is-nft">
                        <div class="form-check form-switch swal2-checkbox" onclick="maxSupplyDisplay()">
                            <input name="is_nft" class="form-check-input" type="checkbox" id="flexSwitchCheckDefault">
                            <label class="form-check-label pl-1" for="flexSwitchCheckDefault">NFT Trohpy</label>
                        </div>
                    </div>
                    <div class="col-5 mb-2 fv-plugins-icon-container hidden" id="max-nft-supply">
                        <label class="form-label" for="modalCreateTrophyPrice"> Max Supply *</label>
                        <div class="input-group input-group-merge">
                            <input required type="number" class="form-control" name="max_supply" value="1"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('max_supply'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-7 form-group" style="align-content: center; margin-top: 1rem" id="trophy-is-nft">
                        <div class="form-check form-switch swal2-checkbox" onclick="maxNFTKeyCreate()">
                            <input name="is_key" class="form-check-input" type="checkbox" id="flexSwitchCheckKeyCreate">
                            <label class="form-check-label pl-1" for="flexSwitchCheckKeyCreate">NFT Key</label>
                        </div>
                    </div>
                    <div class="col-3 mb-2 fv-plugins-icon-container hidden" id="max-nft-key-create">

                            <label class="form-label" for="modalCreatechestBadges"> Key *</label>

                            <select class="js-create-key-select" name="key">
                                <option value="" disabled selected>Select items</option>
                                @foreach($keys as $key)
                                    <option value="{{$key->id}}">{{$key->name}}</option>
                                @endforeach
                            </select>
                    </div>


                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyTitle"> Title *</label>
                        <div class="input-group input-group-merge">
                            <input required type="text" class="form-control" name="name"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('name'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyPrice"> Price(ambars) *</label>
                        <div class="input-group input-group-merge">
                            <input required type="number" class="form-control" name="price"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('price'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateTrophyPrice"> Receive(uru) *</label>
                        <div class="input-group input-group-merge">
                            <input required type="number" class="form-control" name="receive"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('receive'){{ $message }}@enderror</strong>
                        </span>
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
    b {
        display: none;
    }
    .image-selected {
        object-fit: cover;
    }
    label {
        font-size: 1.125rem;
    }
    .trophy-image {
        object-fit: cover;
        overflow: hidden;
        border-radius: 5px;
        background-color: lightgray;
        display: none;
    }
    .trophy-image-alt {
        height: 200px;
        width: 200px;
        border-radius: 5px;
        background-color: lightgray;
        display: block;
    }

    .trophy {
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

    function maxNFTKeyCreate() {
        let isNft = document.getElementById('flexSwitchCheckKeyCreate').checked;
        let maxSupplyInput = document.getElementById('max-nft-key-create');
        if (isNft) {
            maxSupplyInput.classList.remove('hidden');
            maxSupplyInput.setAttribute('required', 'required');
        } else {
            maxSupplyInput.classList.add('hidden');
            maxSupplyInput.removeAttribute('required');
        }
        console.log(maxSupplyInput)

    }
    const inputImg = document.querySelector('.input-img');

    inputImg.addEventListener('change', () => {
        document.querySelector('.trophy-image-alt').style.display = 'none';
        document.querySelector('.trophy-image').style.display = 'block';
    });
</script>

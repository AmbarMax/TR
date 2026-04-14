<div class="modal show show-trophy-modal fade" id="show-trophy-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
                <div class="col-12 text-center"></div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            <div class="modal-body">
                    <div class="col-12 mb-2">
                        <div class="form-group" id="trophy-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-trophy-image">
                                <img id="image" class="trophy image-selected">
                            </div>
                        </div>
                    </div>



                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <div class="design-group mb-50 js-trophy-show">
                        </div>
                    </div>


                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>Title:</label>
                        <div class="trophy-data show-title"></div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>NFT Key:</label>
                        <div class="trophy-data show-key-name"></div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>Price:</label>
                        <div class="trophy-data show-price"></div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>Receive:</label>
                        <div class="trophy-data show-receive"></div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>Description:</label>
                        <div class="trophy-data show-description"></div>
                    </div>
                <div class="col-12 d-flex flex-row">
                    <div class="w-100 text-center" style="margin:0 20px;">
                        <button type="button" class="btn btn-light w-100 js-edit-trophy-btn"
                                id="edit-btn"
                                data-url="{{route('admin.trophies.show', ['id' => 0])}}"
                                data-method="POST">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24">
                                <path d="M7.127 22.562l-7.127 1.438 1.438-7.128 5.689 5.69zm1.414-1.414l11.228-11.225-5.69-5.692-11.227 11.227 5.689 5.69zm9.768-21.148l-2.816 2.817 5.691 5.691 2.816-2.819-5.691-5.689z"/>
                            </svg>

                            <span>Edit</span>
                        </button>
                    </div>

                    <div class="w-100 text-center" style="margin:0 20px;">
                        <button type="button" class="btn btn-danger w-100 js-confirmation-swal"
                                id="delete-btn"
                                data-url="{{route('admin.trophies.delete', ['id' => $trophy->id])}}"
                                data-method="GET">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            <span>Remove</span>
                        </button>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>


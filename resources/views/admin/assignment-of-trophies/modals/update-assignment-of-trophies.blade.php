<div class="modal show new-balance-modal fade" id="edit-assignment-of-trophies-modal" tabindex="-1" aria-modal="true" role="dialog" >
    <div class="modal-dialog modal-sm modal-simple modal-edit-balance">
        <div class="modal-content p-2 p-md-2">
            <div class="modal-header bg-white">
                <div class="col-12 text-center bg-white">
                    <h2>Update user trophies</h2>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="assignment-of-trophies" class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework" enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-2 fv-plugins-icon-container" style="margin-top: 20px;">
                        <label class="form-label" for="modalCreateTrophyBadges"> Trophies</label>
                        <select class="js-edit-trophy-select" name="trophies[]">
                            <option value="" disabled selected>Select badges</option>
                            @foreach($trophies as $trophy)
                                <option value="{{$trophy->id}}">{{$trophy->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Update</button>
                        <button type="reset" class="btn btn-label-secondary waves-effect" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            Cancel
                        </button>
                    </div>
                    <input type="hidden"></form>
            </div>
        </div>
    </div>
</div>

<style>

    label {
        font-size: 1rem;
    }
    h2 {
        font-weight: 600;
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

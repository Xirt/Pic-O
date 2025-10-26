
<p>On this page, you can customize and manage key configurations for your photo gallery. Use this page to tailor the Pic-O experience to best suit your needs and ensure everything runs smoothly for your users. Make sure to save any changes before leaving the page.</p>

<div class="mx-lg-5 mt-5">

    <form action="{{ route('api.settings.store') }}" method="POST" id="settingsForm">
    @csrf

        <div class="mb-3 row align-items-center">

            <label for="site_name" class="col-sm-3 col-form-label">Gallery Name</label>

            <div class="col-sm-9">

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-fonts"></i>
                    </span>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="{{ config('settings.site_name') }}" required>
                </div>

            </div>

        </div>

        <div class="mb-3 row align-items-center">

            <label for="media_root" class="col-sm-3 col-form-label">Photo directory</label>

            <div class="col-sm-9">

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-fonts"></i>
                    </span>
                    <input type="text" class="form-control" id="media_root" name="media_root" value="{{ config('settings.media_root') }}" required>
                </div>

            </div>
        </div>

        <div class="mb-3 row align-items-center">

            <label for="image_rendering" class="col-sm-3 col-form-label">Image rendering</label>

            <div class="col-sm-9">

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-list"></i>
                    </span>
                    <select class="form-select" id="image_rendering" name="downscale_renders" required>
                        <option value="1" {{ config('settings.downscale_renders')  ? 'selected' : '' }}>Downscaled</option>
                        <option value="0" {{ !config('settings.downscale_renders') ? 'selected' : '' }}>Original</option>
                    </select>

                </div>

            </div>
        </div>

        <div class="mb-3 row align-items-center">

            <label for="session_persistent" class="col-sm-3 col-form-label">Login Type</label>

            <div class="col-sm-9">

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-list"></i>
                    </span>
                    <select class="form-select" id="session_persistent" name="session_persistent" required>
                        <option value="1" {{ config('settings.session_persistent')  ? 'selected' : '' }}>Persistent</option>
                        <option value="0" {{ !config('settings.session_persistent') ? 'selected' : '' }}>Short</option>
                    </select>

                </div>

            </div>
        </div>

        <div class="mb-3 alert form-message" id="form-message" aria-live="polite"></div>

        <hr />

        <div class="d-flex justify-content-center mt-4 mb-3 w-lg-m500 mx-auto">

            <button type="submit" class="btn btn-primary w-50 mx-4">
                <i class="bi bi-box-arrow-in-right me-2"></i> Save
            </button>
            <button type="reset" class="btn btn-secondary w-50 mx-4">
                <i class="bi bi-skip-backward-fill me-2"></i> Reset
            </button>

        </div>

    </form>

</div>
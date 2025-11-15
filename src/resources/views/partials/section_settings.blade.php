
<p>On this page, you can customize and manage key configurations for your photo gallery. Use this page to tailor the Pic-O experience to best suit your needs and ensure everything runs smoothly for your users. Make sure to save any changes before leaving the page.</p>

<div class="mx-lg-5 mt-5">

    <form action="{{ route('api.settings.store') }}" method="POST" id="settingsForm">
    @csrf

        <div class="mb-1 row align-items-center">

            <label for="site_name" class="col-md-4 col-form-label">Gallery Name</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">
                    <span class="input-group-text">
                        <i class="bi bi-fonts"></i>
                    </span>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="{{ config('settings.site_name') }}" required>
                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="media_root" class="col-md-4 col-form-label">Photo directory</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">
                    <span class="input-group-text">
                        <i class="bi bi-fonts"></i>
                    </span>
                    <input type="text" class="form-control" id="media_root" name="media_root" value="{{ config('settings.media_root') }}" required>
                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="force_rescan" class="col-md-4 col-form-label">Force rescan</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-list"></i>
                    </span>
                    <select class="form-select" id="force_rescan" name="force_rescan" required>
                        <option value="0" {{ !config('settings.force_rescan')  ? 'selected' : '' }}>On file update</option>
                        <option value="1" {{ config('settings.force_rescan') ? 'selected' : '' }}>Always</option>
                    </select>

                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="scanner_interval" class="col-md-4 col-form-label">Scanner Interval</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-clock"></i>
                    </span>
                    <select class="form-select" id="scanner_interval" name="scanner_interval" required>
                        <option value="none" {{ config('settings.scanner_interval') == 'none' ? 'selected' : '' }}>None</option>
                        <option value="hourly" {{ config('settings.scanner_interval') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                        <option value="daily" {{ config('settings.scanner_interval') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ config('settings.scanner_interval') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ config('settings.scanner_interval') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>

                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="scanner_time" class="col-md-4 col-form-label">Scanner Time (HH:00)</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-alarm"></i>
                    </span>
                    <select class="form-select" id="scanner_time" name="scanner_time" required>
                        @for ($h = 0; $h < 24; $h++)
                            @php $hour = sprintf('%02d:00', $h); @endphp
                            <option value="{{ $hour }}" {{ config('settings.scanner_time') == $hour ? 'selected' : '' }}>
                                {{ $hour }}
                            </option>
                        @endfor
                    </select>

                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="scanner_day_week" class="col-md-4 col-form-label">Scanner Day of Week</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-calendar-week"></i>
                    </span>

                    <select class="form-select" id="scanner_day_week" name="scanner_day_week" required>
                        @php
                            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        @endphp
                        @foreach($days as $index => $day)
                            <option value="{{ $index }}" {{ intval(config('settings.scanner_day_week')) === $index ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>

                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="scanner_day_month" class="col-md-4 col-form-label">Scanner Day of Month</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-calendar-date"></i>
                    </span>
                    <select class="form-select" id="scanner_day_month" name="scanner_day_month" required>
                        @for ($d = 1; $d <= 31; $d++)
                            <option value="{{ $d }}" {{ intval(config('settings.scanner_day_month')) === $d ? 'selected' : '' }}>
                                {{ $d }}
                            </option>
                        @endfor
                    </select>

                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="image_rendering" class="col-md-4 col-form-label">Image rendering</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

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

        <div class="mb-1 row align-items-center">

            <label for="cache_renders" class="col-md-4 col-form-label">Image caching</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-list"></i>
                    </span>
                    <select class="form-select" id="cache_renders" name="cache_renders" required>
                        <option value="0" {{ !config('settings.cache_renders')  ? 'selected' : '' }}>Thumbnails</option>
                        <option value="1" {{ config('settings.cache_renders') ? 'selected' : '' }}>Thumbnails & Renders</option>
                    </select>

                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="session_persistent" class="col-md-4 col-form-label">Login Type</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

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

        <div class="mb-1 row align-items-center">

            <label for="album_name_tpl" class="col-md-4 col-form-label">Album Name Template</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">
                    <span class="input-group-text">
                        <i class="bi bi-fonts"></i>
                    </span>
                    <input type="text" class="form-control" id="album_name_tpl" name="album_name_tpl" value="{{ config('settings.album_name_tpl') }}" required>
                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="album_sorting_type" class="col-md-4 col-form-label">Sort Field</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-list"></i>
                    </span>
                    <select class="form-select" id="album_sorting_type" name="album_sorting_type" required>
                        <option value="name" {{ !config('settings.album_sorting_type') == 'name'  ? 'selected' : '' }}>Album name</option>
                        <option value="type" {{ config('settings.album_sorting_type') == 'type' ? 'selected' : '' }}>Album type</option>
                        <option value="start_date" {{ config('settings.album_sorting_type') == 'start_date' ? 'selected' : '' }}>Album start date</option>
                        <option value="photos_count" {{ config('settings.album_sorting_type') == 'photos_count' ? 'selected' : '' }}>Album photo count</option>
                    </select>

                </div>

            </div>

        </div>

        <div class="mb-1 row align-items-center">

            <label for="album_sorting_direction" class="col-md-4 col-form-label">Sort Direction</label>

            <div class="col-md-8">

                <div class="input-group input-group-sm">

                    <span class="input-group-text">
                        <i class="bi bi-list"></i>
                    </span>
                    <select class="form-select" id="album_sorting_direction" name="album_sorting_direction" required>
                        <option value="asc" {{ !config('settings.album_sorting_direction')  ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ config('settings.album_sorting_direction') ? 'selected' : '' }}>Descending</option>
                    </select>

                </div>

            </div>

        </div>

        <div class="mt-3 alert form-message" id="form-message" aria-live="polite"></div>

        <hr />

        <div class="d-flex justify-content-center mt-4 mb-1 w-lg-m500 mx-auto">

            <button type="submit" class="btn btn-sm btn-primary w-50 w-lg-25 mx-4">
                <i class="bi bi-box-arrow-in-right me-2"></i> Save
            </button>
            <button type="reset" class="btn btn-sm btn-secondary w-50 w-lg-25 mx-4">
                <i class="bi bi-skip-backward-fill me-2"></i> Reset
            </button>

        </div>

    </form>

</div>
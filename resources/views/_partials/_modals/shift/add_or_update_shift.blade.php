<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOrUpdateShift"
    aria-labelledby="offcanvasCreateShiftLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasShiftLabel" class="offcanvas-title">@lang('Create Shift')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
        <form class="pt-0" id="shiftForm">
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="status" id="status">
            <div class="mb-6">
                <label class="form-label" for="name">@lang('Name')<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" placeholder="@lang('Enter name')"
                    name="name" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="code">@lang('Code')<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" placeholder="@lang('Enter code')"
                    name="code" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="startTime">@lang('Start Time')</label>
                <input type="time" class="form-control" id="startTime" placeholder="@lang('Enter start time')"
                    name="startTime" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="endTime">@lang('End Time')</label>
                <input type="time" class="form-control" id="endTime" placeholder="@lang('Enter end time')"
                    name="endTime" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="notes">@lang('Description')</label>
                <textarea class="form-control" id="notes" placeholder="@lang('Enter description')" name="notes"></textarea>
            </div>
            <div class="mb-3">
                <label class="control-label">@lang('Shift Days')</label>
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label mb-0" for="sunday">@lang('Sunday')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="sundayToggle">
                    <input type="hidden" name="sunday" id="sunday" value="0">
                </div>
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label mb-0" for="monday">@lang('Monday')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="mondayToggle" checked>
                    <input type="hidden" name="monday" id="monday" value="1">
                </div>
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label mb-0" for="tuesday">@lang('Tuesday')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="tuesdayToggle" checked>
                    <input type="hidden" name="tuesday" id="tuesday" value="1">
                </div>
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label mb-0" for="wednesday">@lang('Wednesday')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="wednesdayToggle" checked>
                    <input type="hidden" name="wednesday" id="wednesday" value="1">
                </div>
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label mb-0" for="thursday">@lang('Thursday')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="thursdayToggle" checked>
                    <input type="hidden" name="thursday" id="thursday" value="1">
                </div>
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label mb-0" for="friday">@lang('Friday')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="fridayToggle" checked>
                    <input type="hidden" name="friday" id="friday" value="1">
                </div>
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label mb-0" for="saturday">@lang('Saturday')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="saturdayToggle" checked>
                    <input type="hidden" name="saturday" id="saturday" value="1">
                </div>
            </div>
            <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Create')</button>
            <button type="reset" class="btn btn-label-danger"
                data-bs-dismiss="offcanvas">@lang('Cancel')</button>
        </form>
    </div>
</div>

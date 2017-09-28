<div class="uk-modal-dialog">
    <button type="button" class="uk-modal-close uk-close"></button>
    <div class="uk-modal-header">
        <h2>Add Site</h2>
    </div>

    <form class="uk-form uk-form-horizontal" id="user-sites-form">
        <fieldset data-uk-margin>
            <div class="uk-form-row">
                <label for="site_select" class="uk-form-label">Site</label>
                <?php
                $userSite = array();
                foreach($user_sites as $user_site){
                    $userSite[] = $user_site->site_id;
                }
                ?>
                <select name="sites">
                    @foreach($sites as $site)
                        @if(!in_array($site->id,$userSite))
                        <option value="{{$site->id}}">{{$site->site_name}}</option>
                        @endif
                    @endforeach
                    {{--<option selected>Mercury Drug - Site A</option>--}}
                    {{--<option>Robinsons - Site A</option>--}}
                    {{--<option>Robinsons - Site B</option>--}}
                </select>
            </div>
            <div class="uk-form-row">
                <label class="uk-form-label">Permissions</label>
                {{--<label><input type="checkbox" checked> Upload</label>--}}
                {{--<label><input type="checkbox" checked> Review</label>--}}
                {{--<label><input type="checkbox" checked> Accept</label>--}}
                {{--<label><input type="checkbox" checked> Reject</label>--}}
                {{--<label><input type="checkbox" checked> Send</label>--}}
                @foreach($permissions as $permission)
                    <label><input value="{{$permission->id}}" name="permission[]" type="checkbox"> {{ucfirst($permission->permission_name)}}</label>
                @endforeach
            </div>
            <input type="hidden" value="{{$user_id}}" name="user_id" />
        </fieldset>
    </form>

    <div class="uk-modal-footer uk-text-right">
        <button type="button" class="uk-button uk-modal-close">Cancel</button>
        <button type="button" class="uk-button uk-button-primary toggle-save-user-sites">OK</button>
    </div>
</div>
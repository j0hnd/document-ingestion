<div class="uk-modal-dialog">
    <button type="button" class="uk-modal-close uk-close"></button>
    <div class="uk-modal-header">
        <h2>Edit Site</h2>
    </div>

    <form class="uk-form uk-form-horizontal" id="user-sites-form-update">
        <fieldset data-uk-margin>
            <div class="uk-form-row">
                <label for="site_select" class="uk-form-label">Site</label>
                {{--<select name="sites">--}}
                    {{--@foreach($sites as $site)--}}
                        {{--<option {{$get_site->site_id == $site->id ? "selected" : ""}} value="{{$site->id}}">{{$site->site_name}}</option>--}}
                    {{--@endforeach--}}
                {{--</select>--}}
                @foreach($sites as $site)
                {{$get_site->site_id == $site->id ? $site->site_name : ""}}
                @endforeach
                <input type="hidden" value="{{$user_site_id}}" name="user_site_id" />
            </div>
            <div class="uk-form-row">
                <label class="uk-form-label">Permissions</label>
                <?php
                $as = array();
                foreach($get_user_permission_per_id as $user_permission){
                    $as[] = $user_permission->id;
                }
                ?>
                @foreach($permissions as $permission)
                    <label><input {{in_array($permission->id,$as) ? "checked" : ""}} value="{{$permission->id}}" name="permission[]" type="checkbox"> {{ucfirst($permission->permission_name)}}</label>
                @endforeach
            </div>
        </fieldset>
    </form>

    <div class="uk-modal-footer uk-text-right">
        <button type="button" class="uk-button uk-modal-close">Cancel</button>
        <button type="button" class="uk-button uk-button-primary toggle-update-user-sites">OK</button>
    </div>
</div>
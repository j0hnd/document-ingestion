{{--* / $permissionsArr = array(); /*--}}
<?php $permissions = ''; ?>
@forelse($user_sites as $site)
    <tr>
        <td>
            {{$site->site_name}}
        </td>
        <td>
            @foreach($user_permissions as $permission)
                @if($site->user_site_id == $permission->user_site_id)
                    <?php $permissions .= ucfirst($permission->permission_name) . ', ' ?>
                @endif
            @endforeach
            {{ trim($permissions, ', ') }}
        </td>
        <td>
            <a href="#" class="toggle-delete-site" data-id="{{$site->user_site_id}}"><i class="uk-icon-trash"></i></a> &nbsp;
            <a class="toggle-update-site" data-id="{{$site->user_site_id}}" href="#edit-site" data-uk-modal><i class="uk-icon-edit"></i></a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center">No available site</td>
    </tr>
@endforelse
<tr>
    <td colspan="3"><a class="toggle-show-add-site" href="#add-edit-site" data-uk-modal><i class="uk-icon-plus-circle"> Add Site</i></a></td>
</tr>
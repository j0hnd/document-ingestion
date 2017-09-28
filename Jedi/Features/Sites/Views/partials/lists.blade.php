@if($sites->count())
    @foreach($sites->get() as $site)
    <tr>
        <td>{{ $site->site_name }}</td>
        <td>
            <a class="toggle-delete-site" href="javascript: void(0);" data-id="{{ $site->site_id }}"><i class="uk-icon-trash"></i></a> &nbsp;
            <a class="toggle-edit-site" href="{{ URL::to('/template/'.$site->site_id.'/edit') }}"><i class="uk-icon-edit"></i></a>
        </td>
    </tr>
    @endforeach
@else
    <tr>
        <td colspan="2">No Input Templates Found</td>
    </tr>
@endif
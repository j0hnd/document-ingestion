@if($users->count())
    @foreach($users->get() as $user)
    <tr>
        <td>
            {!! Form::checkbox('row', $user->user_id, null, ['class' => 'check-user-row uk-margin-small-right', 'id' => 'user-' . $user->user_id]) !!}
            <a href="{{ Url::to('/users/'.$user->user_id.'/edit') }}">{{ $user->firstname }}&nbsp;{{ $user->lastname }}</a>
        </td>
        <td>{{ $user->type_name }}</td>
        <td></td>
    </tr>
    @endforeach
@else
    <tr>
        <td colspan="3">No users found</td>
    </tr>
@endif
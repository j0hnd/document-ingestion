@if($documents['status'])
    @foreach($documents['data'] as $po)
    <tr id="row-{{ $po['id'] }}">
        <td>{!! HTML::link(URL::to('/document/'.$po['document_id'].'/'.$po['id'].'/process'), $po['document_name']) !!}</td>
        <td>
            @if($po['status'] == 'Pending')
                <i class="uk-icon-dot-circle-o uk-text-primary"> {{ $po['status'] }}</i>
            @elseif($po['status'] == 'Reviewed')
                <i class="uk-icon-exclamation-circle uk-text-warning"> {{ $po['status'] }}</i>
            @elseif($po['status'] == 'Rejected')
                <i class="uk-icon-times-circle uk-text-danger"> {{ $po['status'] }}</i>
            @elseif($po['status'] == 'Accepted' or $po['status'] == 'Send')
                <i class="uk-icon-check-circle uk-text-success"> {{ $po['status'] }}</i>
            @endif
        </td>
        <td> {{ empty($po['checked_by']) ? '--' : $po['checked_by'] }}</td>
        <td>{{ \Carbon\Carbon::parse($po['created_at'])->format('d M Y H:i') }}</td>
    </tr>
    @endforeach
@else
@endif
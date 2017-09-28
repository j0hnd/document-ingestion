@if($batch['status'])
    @foreach($batch['data'] as $_batch)
    <tr id="batch-{{ $_batch['batch_id'] }}">
        <td>{{ $_batch['upload_name'] }}</td>
        <td>{{ $_batch['site_name'] }}</td>
        <td>{{ $_batch['output'] }}</td>
        <td>{{ date('d M Y h:ia', strtotime($_batch['created_at'])) }}</td>
        <td>
            @if($_batch['file_type'] == 'XML')
            <i class="uk-icon-file-pdf-o"> PDF</i>
            @else
            <i class="uk-icon-file-excel-o"> XLS</i>
            @endif
        </td>
        <td class="uk-text-center">{{ $_batch['file_count'] }}</td>
        <td>
            @if($_batch['status'] == 'Pending')
            <i class="uk-icon-dot-circle-o uk-text-primary"> {{ $_batch['status'] }}</i>
            @elseif($_batch['status'] == 'Processing')
            <i class="uk-icon-dot-circle-o uk-text-primary"> {{ $_batch['status'] }}</i>
            @elseif($_batch['status'] == 'Ready')
            <i class="uk-icon-dot-circle-o uk-text-primary"> {{ $_batch['status'] }}</i>
            @elseif($_batch['status'] == 'Reviewed')
            <i class="uk-icon-exclamation-circle uk-text-warning"> {{ $_batch['status'] }}</i>
            @elseif($_batch['status'] == 'Rejected')
            <i class="uk-icon-times-circle uk-text-danger"> {{ $_batch['status'] }}</i>
            @elseif($_batch['status'] == 'Completed')
            <i class="uk-icon-check-circle uk-text-success"> {{ $_batch['status'] }}</i>
            @elseif($_batch['status'] == 'Held')
            <i class="uk-icon-hand-stop-o uk-text-warning"> {{ $_batch['status'] }}</i>
            @else
            {{ $_batch['status'] }}
            @endif
        </td>
        <td>
            @if($_batch['status'] == 'Ready')
            <a href="{{ URL::to('/documents/'.$_batch['batch_id'].'/list') }}" class="uk-button uk-button-mini uk-button-primary">Process</a>
            @elseif($_batch['status'] == 'Reviewed')
            <a href="{{ URL::to('/documents/'.$_batch['batch_id'].'/list') }}" class="uk-button uk-button-mini uk-button-primary">Review</a>
            <a href="javascript:void(0)" class="uk-button uk-button-mini uk-button-danger toggle-batch-status" data-batch="{{ $_batch['batch_id'] }}" data-status="Rejected" data-status-label="reject">Reject</a>
            @elseif($_batch['status'] == 'Rejected')
            <a href="{{ URL::to('/documents/'.$_batch['batch_id'].'/list') }}" class="uk-button uk-button-mini uk-button-primary">Review</a>
            <a href="javascript:void(0)" class="uk-button uk-button-mini uk-button-danger toggle-batch-status" data-batch="{{ $_batch['batch_id'] }}" data-status="Removed" data-status-label="remove">Remove</a>
            @elseif($_batch['status'] == 'Completed')
            <a href="javascript:void(0)" class="uk-button uk-button-mini uk-button-success toggle-batch-status" data-batch="{{ $_batch['batch_id'] }}" data-status="Send" data-status-label="send">Send</a>
            <a href="javascript:void(0)" class="uk-button uk-button-mini uk-button-danger toggle-batch-status" data-batch="{{ $_batch['batch_id'] }}" data-status="Held" data-status-label="hold">Hold</a>
            @elseif($_batch['status'] == 'Held')
            <a href="{{ URL::to('/documents/'.$_batch['batch_id'].'/list') }}" class="uk-button uk-button-mini uk-button-primary">Review</a>
            <a href="javascript:void(0)" class="uk-button uk-button-mini uk-button-success toggle-batch-status" data-batch="{{ $_batch['batch_id'] }}" data-status="Send" data-status-label="send">Send</a>
            @endif
        </td>
    </tr>
    @endforeach
@else
    <tr>
        <td colspan="8">No documents found</td>
    </tr>
@endif
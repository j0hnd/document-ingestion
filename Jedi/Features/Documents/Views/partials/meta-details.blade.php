{{--<div class="uk-panel uk-panel-box jedi-review-panel">--}}
    {{--<div class="uk-panel-title uk-text-small jedi-review-header">HEADER</div>--}}
    {{--<form class="uk-form uk-form-horizontal">--}}
        {{--<fieldset data-uk-margin>--}}
            {{--<div class="uk-form-row">--}}
                {{--<label for="head_vendor" class="uk-form-label">Vendor</label>--}}
                {{--<input type="text" name="head_vendor" id="head_vendor" value="100006" class="uk-form-small uk-form-blank">--}}
            {{--</div>--}}
            {{--<div class="uk-form-row">--}}
                {{--<label for="head_po_number" class="uk-form-label">PO Number</label>--}}
                {{--<input type="text" name="head_po_number" id="head_po_number" value="639015" class="uk-form-small uk-form-blank">--}}
            {{--</div>--}}
            {{--<div class="uk-form-row">--}}
                {{--<label for="head_approved_date" class="uk-form-label">Approved Date</label>--}}
                {{--<input type="text" name="head_approved_date" id="head_approved_date" value="6 Feb 2015" class="uk-form-small uk-form-blank">--}}
            {{--</div>--}}
            {{--<div class="uk-form-row">--}}
                {{--<label for="head_delivery_date" class="uk-form-label">Delivery Date</label>--}}
                {{--<input type="text" name="head_delivery_date" id="head_delivery_date" value="11 Feb 2015" class="uk-form-small uk-form-blank">--}}
            {{--</div>--}}
            {{--<div class="uk-form-row">--}}
                {{--<label for="head_cancel_date" class="uk-form-label">Cancel Date</label>--}}
                {{--<input type="text" name="head_cancel_date" id="head_cancel_date" value="12 Feb 2015" class="uk-form-small uk-form-blank">--}}
            {{--</div>--}}
        {{--</fieldset>--}}

    {{--</form>--}}
{{--</div>--}}

<div class="uk-panel uk-panel-box jedi-review-panel">
    @if($meta['status'])
        @foreach($meta['data'] as $data)
            <form class="uk-form uk-form-horizontal uk-margin-bottom">
            @foreach($data as $key => $meta)
                @if($meta['key'] == 'jj_price')
                <?php $unit_price = (float) $meta['value'] ?>
                @endif

                @if($key == 'item_description')
                <div class="uk-panel-title uk-text-small uk-text-bold">{{ $meta['value'] }}</div>
                @else
                <fieldset data-uk-margin>
                    <div class="uk-form-row">
                        <label for="line1_sku" class="uk-form-label uk-text-small">{{ strtoupper(str_replace('_', ' ', $meta['key'])) }}</label>
                        @if($meta['key'] == 'jj_price')
                        <input type="text" name="{{ $meta['key'] }}" id="{{ $meta['key'] }}" value="{{ number_format($unit_price, 2, '.', ',') }}" class="uk-form-small uk-form-blank">
                        @else
                        <input type="text" name="{{ $meta['key'] }}" id="{{ $meta['key'] }}" value="{{$meta['value']}}" class="uk-form-small uk-form-blank">
                        @endif
                    </div>

                    @if($meta['key'] == 'total_order')
                        <?php $total = $meta['value'] * $unit_price ?>
                        <div class="uk-form-row">
                            <label for="line1_sku" class="uk-form-label uk-text-small">TOTAL AMOUNT</label>
                            <input type="text" name="total_amount" id="total_amount" value="{{ number_format($total, 2, '.', ',') }}" class="uk-form-small uk-form-blank">
                        </div>
                    @endif
                </fieldset>
                @endif
            @endforeach
            </form>
        @endforeach
    @endif
</div>
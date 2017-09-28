<form id='map-details' style="width: 420px; height:100%; border:0">
    <fieldset>
        <legend id='legend'><i>Please enter line numbers of the following:</i></legend>
        
        <table>
            <tr height="40px">
                <td><label>Company Name</label></td>
                <td>
                    <input type="text" name="company" id="name"  readonly disable style="width: 160px; border:none;font-size: 17px;font-weight: bold;text-transform: capitalize;"/>
                    <input type="hidden" id="company_name" name="company_name"/>
                    <input type="hidden" id="extension" name="ext"/>
                    <input type="hidden" id="numlines" name="numlines"/>
                    <input type="hidden" id="query" name="query"/>
                    <input type="hidden" id="count" name="count"/>
                    <input type="hidden" id="selected" name="selected"/>
                    <input type="hidden" id="url" name="url"/>

                </td>
            </tr>
        </table>

        <div id='div_details' class='get_id' name = 'div_details'>
            <table>
                <div id='div_sample'>
                    @foreach($selected as $row)
                        @if($row->type == '1')
                        <tr height='5px'></tr>
                        <tr height='20px'>
                            <td><label><b>* {{ ucfirst($row->fieldname) }}</b></label></td>
                            <td><input type="text" id="{{$row->fieldname}}" class="numbers" name="{{$row->fieldname}}" style="text-transform:uppercase; width: 190px" /></td>
                        </tr>
                            <td colspan=2><input type='text' id='dup-{{$row->fieldname}}' style="width: 80px;float:right; display:none;border:none;color:red;font-size:10px" readonly/></td>
                        @endif
                    @endforeach

                    @foreach($selected as $row)
                        @if($row->type == '0')
                        <tr height='5px'></tr>
                        <tr height='20px'>
                            <td><label>{{ ucfirst($row->fieldname) }}</label></td>
                            <td><input type="text" id="{{$row->fieldname}}" class="numbers" name="{{$row->fieldname}}" style="text-transform:uppercase; width: 190px" /></td>
                        </tr>
                            <td colspan=2><input type='text' id='dup-{{$row->fieldname}}' style="width: 80px;float:right; display:none;border:none;color:red;font-size:10px" readonly/></td>
                        @endif
                    @endforeach
                </div>

                <tr height="40px">
                    <td colspan=2><center><input type="button" id="create-map" value="Submit"></center></td>
                </tr>
                <tr> 
                    <td colspan=2><i id="refreshtext" style='display:none;'>if data didn't appear, Please click <a id="refresh" >refresh</a></i></td>
                </tr>
            </table>
        </div>
    </fieldset>
</form>